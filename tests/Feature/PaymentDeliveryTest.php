<?php

namespace Tests\Feature;

use App\Events\OrderPaidEvent;
use App\Listeners\SendPurchaseConfirmationListener;
use App\Mail\PurchaseConfirmationMail;
use App\Services\OrderPaidOutbox;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Mail\MailManager;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Mail;
use Tests\Support\FinancialFixtures;
use Tests\TestCase;

class PaymentDeliveryTest extends TestCase
{
    use FinancialFixtures, RefreshDatabase;

    public function test_confirmation_listener_retries_do_not_resend_recorded_mail(): void
    {
        Mail::fake();
        $ledger = $this->financialLedger($this->financialAuthor());
        $order = $ledger->orderItem->order;
        $listener = new SendPurchaseConfirmationListener;
        $listener->handle(new OrderPaidEvent($order));
        $listener->handle(new OrderPaidEvent($order));
        Mail::assertSentCount(1);
        Mail::assertSent(PurchaseConfirmationMail::class);
        $this->assertNotNull($order->fresh()->confirmation_sent_at);
    }

    public function test_outbox_retains_failed_delivery_and_retry_preserves_paid_order(): void
    {
        $ledger = $this->financialLedger($this->financialAuthor());
        $order = $ledger->orderItem->order;
        $outbox = app(OrderPaidOutbox::class);
        Mail::shouldReceive('to')->andThrow(new \RuntimeException('Synthetic mail transport failure'));
        $outbox->record($order);
        $this->assertFalse($outbox->dispatch($order->id));
        $this->assertSame('success', $order->fresh()->status);
        $this->assertNull($order->fresh()->confirmation_sent_at);
        $this->assertDatabaseHas('order_paid_outbox', ['order_id' => $order->id, 'dispatched_at' => null]);
        Mail::swap(new MailManager($this->app));
        Mail::fake();
        $this->assertTrue($outbox->dispatch($order->id));
        $this->assertTrue($outbox->dispatch($order->id));
        Mail::assertSentCount(1);
        $this->assertNotNull(DB::table('order_paid_outbox')->where('order_id', $order->id)->value('dispatched_at'));
        $this->assertDatabaseCount('royalty_ledgers', 1);
    }

    public function test_database_queue_worker_delivers_durable_events_once(): void
    {
        config(['queue.default' => 'database', 'queue.connections.database.after_commit' => false]);
        Mail::fake();
        $order = $this->financialLedger($this->financialAuthor())->orderItem->order;
        $outbox = app(OrderPaidOutbox::class);
        $outbox->record($order);
        $this->assertTrue($outbox->dispatch($order->id));
        $this->assertDatabaseCount('jobs', 2);
        $this->artisan('queue:work', ['connection' => 'database', '--stop-when-empty' => true, '--tries' => 1])->assertExitCode(0);
        $this->assertDatabaseCount('jobs', 0);
        $this->assertDatabaseCount('failed_jobs', 0);
        $this->assertDatabaseCount('royalty_ledgers', 1);
        Mail::assertSentCount(1);
        $this->assertNotNull($order->fresh()->confirmation_sent_at);
    }

    public function test_transaction_rollback_discards_outbox_record(): void
    {
        $ledger = $this->financialLedger($this->financialAuthor());
        $order = $ledger->orderItem->order;
        try {
            DB::transaction(function () use ($order) {
                app(OrderPaidOutbox::class)->record($order);
                throw new \RuntimeException('Synthetic rollback');
            });
        } catch (\RuntimeException $e) {
            $this->assertDatabaseCount('order_paid_outbox', 0);
        }
    }
}
