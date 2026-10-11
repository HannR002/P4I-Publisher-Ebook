<?php

namespace Tests\Feature;

use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Str;
use Tests\Support\FinancialFixtures;
use Tests\Support\RebuildsIsolatedDatabase;
use Tests\TestCase;

class PayoutMigrationTest extends TestCase
{
    use FinancialFixtures, RebuildsIsolatedDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->rebuildIsolatedDatabase();
    }

    private function migration()
    {
        return require database_path('migrations/2026_10_11_000001_create_payout_allocations.php');
    }

    public function test_clean_migration_rolls_back_and_reapplies(): void
    {
        $migration = $this->migration();
        $migration->down();
        $this->assertFalse(Schema::hasTable('payout_allocations'));
        $migration->up();
        $this->assertTrue(Schema::hasTable('payout_allocations'));
    }

    public function test_all_new_migrations_can_roll_back_and_reapply_on_clean_database(): void
    {
        $paths = [
            '2026_10_11_000001_create_payout_allocations.php',
            '2026_10_11_000002_expand_encrypted_financial_columns.php',
            '2026_10_11_000003_create_order_paid_outbox.php',
        ];
        foreach (array_reverse($paths) as $path) {
            (require database_path('migrations/'.$path))->down();
        }
        $this->assertFalse(Schema::hasTable('payout_allocations'));
        $this->assertFalse(Schema::hasTable('order_paid_outbox'));
        $this->assertFalse(Schema::hasColumn('orders', 'confirmation_sent_at'));
        foreach ($paths as $path) {
            (require database_path('migrations/'.$path))->up();
        }
        $this->assertTrue(Schema::hasTable('payout_allocations'));
        $this->assertTrue(Schema::hasTable('order_paid_outbox'));
        $this->assertTrue(Schema::hasColumn('orders', 'confirmation_sent_at'));
    }

    public function test_existing_full_payout_is_backfilled_without_mutating_ledger(): void
    {
        $migration = $this->migration();
        $migration->down();
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author, '100000.00');
        $payout = PayoutRequest::create(['author_id' => $author->id, 'amount' => 100000, 'status' => 'completed']);
        DB::table('royalty_ledgers')->where('id', $ledger->id)->update(['status' => 'withdrawn', 'payout_request_id' => $payout->id]);
        $before = (array) DB::table('royalty_ledgers')->where('id', $ledger->id)->first();
        $migration->up();
        $this->assertDatabaseHas('payout_allocations', ['payout_request_id' => $payout->id, 'royalty_ledger_id' => $ledger->id, 'amount' => 100000]);
        $this->assertSame($before, (array) DB::table('royalty_ledgers')->where('id', $ledger->id)->first());
        $this->assertSame('0.00', $author->fresh()->available_balance);
    }

    public function test_inconsistent_historical_payout_aborts_before_schema_changes(): void
    {
        $migration = $this->migration();
        $migration->down();
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author, '100000.00');
        $payout = PayoutRequest::create(['author_id' => $author->id, 'amount' => 110000, 'status' => 'requested']);
        DB::table('royalty_ledgers')->where('id', $ledger->id)->update(['status' => 'pending', 'payout_request_id' => $payout->id]);
        try {
            $migration->up();
            $this->fail('Inconsistent historical payout accepted.');
        } catch (\RuntimeException $e) {
            $this->assertFalse(Schema::hasTable('payout_allocations'));
            $this->assertFalse(Schema::hasColumn('payout_requests', 'idempotency_key'));
        } finally {
            DB::table('royalty_ledgers')->where('id', $ledger->id)->update(['status' => 'available', 'payout_request_id' => null]);
            DB::table('payout_requests')->where('id', $payout->id)->delete();
            $migration->up();
        }
    }

    public function test_rollback_refuses_to_drop_new_financial_data(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        try {
            $this->migration()->down();
            $this->fail('Financial data dropped.');
        } catch (\RuntimeException $e) {
            $this->assertDatabaseCount('payout_requests', 1);
            $this->assertDatabaseCount('payout_allocations', 1);
        }
    }
}
