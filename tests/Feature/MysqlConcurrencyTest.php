<?php

namespace Tests\Feature;

use App\Jobs\ProcessMidtransWebhook;
use App\Models\Author;
use App\Models\PayoutRequest;
use App\Models\User;
use App\Services\PayoutService;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Illuminate\Support\Str;
use Tests\Support\FinancialFixtures;
use Tests\Support\RebuildsIsolatedDatabase;
use Tests\TestCase;

class MysqlConcurrencyTest extends TestCase
{
    use FinancialFixtures, RebuildsIsolatedDatabase;

    protected function setUp(): void
    {
        if (getenv('P4I_MYSQL_CONCURRENCY') !== '1') {
            $this->markTestSkipped('Run with isolated MySQL/MariaDB and P4I_MYSQL_CONCURRENCY=1.');
        }
        parent::setUp();
        $this->rebuildIsolatedDatabase();
        $this->assertSame('mysql', DB::getDriverName());
        $this->assertTrue(function_exists('pcntl_fork'), 'Concurrency runner requires pcntl.');
        Mail::fake();
    }

    private function race(callable $action): array
    {
        $directory = sys_get_temp_dir().'/p4i-race-'.Str::uuid();
        mkdir($directory, 0700);
        DB::purge();
        $pids = [];
        for ($i = 0; $i < 2; $i++) {
            $pid = pcntl_fork();
            if ($pid === -1) {
                $this->fail('Fork failed.');
            }
            if ($pid === 0) {
                DB::purge();
                $deadline = microtime(true) + 10;
                while (! is_file($directory.'/start')) {
                    if (microtime(true) > $deadline) {
                        exit(3);
                    }
                    usleep(1000);
                }
                try {
                    $value = $action($i);
                    $result = ['ok' => true, 'value' => $value];
                } catch (\Throwable $e) {
                    $result = ['ok' => false, 'class' => $e::class];
                }
                file_put_contents($directory.'/'.$i.'.json', json_encode($result));
                DB::disconnect();
                exit(0);
            }
            $pids[] = $pid;
        }
        touch($directory.'/start');
        foreach ($pids as $pid) {
            pcntl_waitpid($pid, $status);
            $this->assertSame(0, pcntl_wexitstatus($status));
        }
        $results = [];
        foreach ([0, 1] as $i) {
            $results[] = json_decode(file_get_contents($directory.'/'.$i.'.json'), true);
            unlink($directory.'/'.$i.'.json');
        }
        unlink($directory.'/start');
        rmdir($directory);
        DB::purge();

        return $results;
    }

    public function test_concurrent_requests_cannot_double_spend(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author, '150000.00');
        $id = $author->id;
        $results = $this->race(fn () => app(PayoutService::class)->requestPayout(Author::findOrFail($id), 100000, (string) Str::uuid())->id);
        $this->assertCount(1, array_filter($results, fn ($r) => $r['ok']));
        $this->assertSame(\DomainException::class, array_values(array_filter($results, fn ($r) => ! $r['ok']))[0]['class']);
        $this->assertDatabaseCount('payout_requests', 1);
        $this->assertDatabaseCount('payout_allocations', 1);
        $this->assertSame('50000.00', $author->fresh()->available_balance);
    }

    public function test_concurrent_duplicate_key_creates_one_payout(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author, '250000.00');
        $id = $author->id;
        $key = (string) Str::uuid();
        $results = $this->race(fn () => app(PayoutService::class)->requestPayout(Author::findOrFail($id), 100000, $key)->id);
        $this->assertTrue($results[0]['ok']);
        $this->assertTrue($results[1]['ok']);
        $this->assertSame($results[0]['value'], $results[1]['value']);
        $this->assertDatabaseCount('payout_requests', 1);
        $this->assertDatabaseCount('payout_audits', 1);
    }

    public function test_competing_admin_actions_have_one_terminal_winner(): void
    {
        $author = $this->financialAuthor();
        $this->financialLedger($author);
        $payout = app(PayoutService::class)->requestPayout($author, 100000, (string) Str::uuid());
        $admin = User::factory()->create(['is_admin' => true]);
        $id = $payout->id;
        $adminId = $admin->id;
        $results = $this->race(function ($i) use ($id, $adminId) {
            $service = app(PayoutService::class);
            $row = PayoutRequest::findOrFail($id);
            $i === 0 ? $service->completePayout($row, 'SYNTHETIC-REF', null, $adminId) : $service->rejectPayout($row, 'Synthetic rejection', $adminId);

            return $row->fresh()->status;
        });
        $this->assertCount(1, array_filter($results, fn ($r) => $r['ok']));
        $this->assertDatabaseCount('payout_audits', 2);
        $this->assertContains($payout->fresh()->status, ['completed', 'rejected']);
    }

    public function test_concurrent_webhooks_grant_one_license_and_one_outbox_record(): void
    {
        $author = $this->financialAuthor();
        $ledger = $this->financialLedger($author);
        $order = $ledger->orderItem->order;
        $order->update(['status' => 'pending']);
        $payload = ['order_id' => $order->id, 'gross_amount' => $order->gross_amount, 'transaction_status' => 'settlement', 'status_code' => '200'];
        $results = $this->race(function () use ($payload) {
            (new ProcessMidtransWebhook($payload))->handle();

            return true;
        });
        $this->assertTrue($results[0]['ok']);
        $this->assertTrue($results[1]['ok']);
        $this->assertDatabaseCount('book_licenses', 1);
        $this->assertDatabaseCount('order_paid_outbox', 1);
    }
}
