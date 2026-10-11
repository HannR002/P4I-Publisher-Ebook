<?php

namespace Tests\Feature;

use App\Models\PayoutAllocation;
use App\Models\User;
use App\Services\PayoutService;
use App\Services\RoyaltyBalanceService;
use DomainException;
use Illuminate\Database\QueryException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use PHPUnit\Framework\Attributes\DataProvider;
use Tests\Support\FinancialFixtures;
use Tests\TestCase;

class PayoutIntegrityTest extends TestCase
{
    use FinancialFixtures, RefreshDatabase;

    public function test_partial_payout_preserves_original_sale_and_reconciles_remaining_balance(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $before = $ledger->fresh()->getAttributes();
        $payout = app(PayoutService::class)->requestPayout($author, '100000.01', (string) Str::uuid());
        $this->assertSame($before, $ledger->fresh()->getAttributes());
        $this->assertDatabaseCount('royalty_ledgers', 1);
        $this->assertSame('100000.01', $payout->allocations->sole()->amount);
        $this->assertSame(['available' => '109999.99', 'pending' => '100000.01', 'withdrawn' => '0.00'], app(RoyaltyBalanceService::class)->balances($author->id));
    }

    public function test_full_payout_and_multiple_fifo_allocations(): void
    {
        $author = $this->financialAuthor();
        $first = $this->financialLedger($author, '70000.00');
        $second = $this->financialLedger($author, '70000.00');
        $third = $this->financialLedger($author, '70000.00');
        $payout = app(PayoutService::class)->requestPayout($author, '100000.00', (string) Str::uuid());
        $this->assertSame([$first->id, $second->id], $payout->allocations->pluck('royalty_ledger_id')->all());
        $this->assertSame(['70000.00', '30000.00'], $payout->allocations->pluck('amount')->all());
        $next = app(PayoutService::class)->requestPayout($author, '110000.00', (string) Str::uuid());
        $this->assertSame([$second->id, $third->id], $next->allocations->pluck('royalty_ledger_id')->all());
        $this->assertSame('0.00', $author->fresh()->available_balance);
    }

    public function test_duplicate_request_returns_same_payout_and_key_reuse_with_different_amount_fails(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $key = (string) Str::uuid();
        $service = app(PayoutService::class);
        $first = $service->requestPayout($author, 100000, $key);
        $second = $service->requestPayout($author, '100000.00', $key);
        $this->assertSame($first->id, $second->id);
        $this->assertDatabaseCount('payout_requests', 1);
        $this->assertDatabaseCount('payout_audits', 1);
        $this->expectException(DomainException::class);
        $service->requestPayout($author, 110000, $key);
    }

    public function test_insufficient_balance_rolls_back_all_financial_writes(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author, '99999.99');
        try {
            app(PayoutService::class)->requestPayout($author, '100000.00', (string) Str::uuid());
            $this->fail('Insufficient balance accepted.');
        } catch (DomainException $e) {
            $this->assertDatabaseCount('payout_requests', 0);
            $this->assertDatabaseCount('payout_allocations', 0);
            $this->assertDatabaseCount('payout_audits', 0);
        }
    }

    public function test_rejection_releases_only_allocation_and_preserves_audit(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $service = app(PayoutService::class);
        $payout = $service->requestPayout($author, 100000, (string) Str::uuid());
        $admin = User::factory()->create(['is_admin' => true]);
        $service->rejectPayout($payout, 'Synthetic rejection', $admin->id);
        $service->rejectPayout($payout, 'Synthetic rejection', $admin->id);
        $this->assertSame('210000.00', $author->fresh()->available_balance);
        $this->assertSame('210000.00', $ledger->fresh()->author_earning);
        $this->assertDatabaseCount('payout_allocations', 1);
        $this->assertSame(['requested', 'rejected'], $payout->audits()->orderBy('id')->pluck('action')->all());
    }

    public function test_completion_is_idempotent_and_cannot_be_rejected_or_reprocessed(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $service = app(PayoutService::class);
        $payout = $service->requestPayout($author, 100000, (string) Str::uuid());
        $admin = User::factory()->create(['is_admin' => true]);
        $service->completePayout($payout, 'TEST-REFERENCE', null, $admin->id);
        $service->completePayout($payout, 'TEST-REFERENCE', null, $admin->id);
        $this->assertSame('100000.00', app(RoyaltyBalanceService::class)->balances($author->id)['withdrawn']);
        $this->assertDatabaseCount('payout_audits', 2);
        foreach (['reject', 'different-reference'] as $action) {
            try {
                $action === 'reject' ? $service->rejectPayout($payout, 'Synthetic rejection', $admin->id) : $service->completePayout($payout, 'DIFFERENT', null, $admin->id);
                $this->fail('Terminal payout changed.');
            } catch (DomainException $e) {
                $this->assertSame('completed', $payout->fresh()->status);
            }
        }
    }

    public function test_rejected_payout_cannot_be_completed(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $service = app(PayoutService::class);
        $payout = $service->requestPayout($author, 100000, (string) Str::uuid());
        $admin = User::factory()->create(['is_admin' => true]);
        $service->rejectPayout($payout, 'Synthetic rejection', $admin->id);
        $this->expectException(DomainException::class);
        $service->completePayout($payout, 'TEST', null, $admin->id);
    }

    public function test_non_admin_cannot_process_payout_through_service(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $payout = app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        $this->expectException(DomainException::class);
        app(PayoutService::class)->completePayout($payout, 'TEST', null, $author->user_id);
    }

    public function test_disabled_feature_has_no_writes(): void
    {
        config(['features.payout' => false]);
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $this->expectException(DomainException::class);
        app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
    }

    public function test_ledger_and_allocation_are_immutable(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $payout = app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        foreach ([$ledger, $payout->allocations->first(), $payout->audits->first()] as $model) {
            try {
                $model->delete();
                $this->fail('Financial record deleted.');
            } catch (DomainException $e) {
                $this->assertTrue($model->fresh()->exists);
            }
        }
        $this->expectException(DomainException::class);
        $ledger->update(['author_earning' => 1]);
    }

    public function test_failure_during_allocation_rolls_back_payout_and_audit(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        PayoutAllocation::creating(fn () => throw new DomainException('Synthetic storage failure'));
        try {
            app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
            $this->fail('Expected allocation failure.');
        } catch (DomainException $e) {
            $this->assertDatabaseCount('payout_requests', 0);
            $this->assertDatabaseCount('payout_allocations', 0);
            $this->assertDatabaseCount('payout_audits', 0);
        } finally {
            PayoutAllocation::flushEventListeners();
            PayoutAllocation::clearBootedModels();
        }
    }

    public function test_database_rejects_duplicate_pair_and_nonpositive_allocation(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $payout = app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        $other = $this->financialLedger($author);
        foreach (['100000.00', '0.00'] as $amount) {
            try {
                DB::transaction(fn () => DB::table('payout_allocations')->insert(['payout_request_id' => $payout->id, 'royalty_ledger_id' => $amount === '0.00' ? $other->id : $ledger->id, 'amount' => $amount]));
                $this->fail('Invalid allocation accepted.');
            } catch (QueryException $e) {
                $this->assertDatabaseCount('payout_allocations', 1);
            }
        }
    }

    public function test_database_blocks_ledger_amount_changes_outside_eloquent(): void
    {
        $ledger = $this->financialLedger($this->financialAuthor());
        try {
            DB::transaction(fn () => DB::table('royalty_ledgers')->where('id', $ledger->id)->update(['author_earning' => 1]));
            $this->fail('Financial ledger update was accepted.');
        } catch (QueryException $e) {
            $this->assertSame('210000.00', $ledger->fresh()->author_earning);
        }
    }

    public function test_database_blocks_rewriting_financial_audit(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        $this->expectException(QueryException::class);
        DB::table('payout_audits')->update(['action' => 'completed']);
    }

    #[DataProvider('invalidAmounts')]
    public function test_invalid_money_is_rejected(string $amount): void
    {
        $this->expectException(DomainException::class);
        app(PayoutService::class)->requestPayout($this->financialAuthor(), $amount, (string) Str::uuid());
    }

    public static function invalidAmounts(): array
    {
        return [['100000.001'], ['1e6'], ['-100000'], ['NaN'], ['10000000000.00'], ['99999.99']];
    }
}
