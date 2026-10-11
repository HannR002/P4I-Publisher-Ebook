<?php

namespace Tests\Feature;

use App\Events\OrderPaidEvent;
use App\Jobs\ProcessMidtransWebhook;
use App\Listeners\RecordAuthorRoyaltyListener;
use App\Models\Book;
use App\Models\Order;
use App\Models\RoyaltyLedger;
use App\Models\User;
use DomainException;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Bus;
use Illuminate\Support\Facades\Event;
use Tests\Support\FinancialFixtures;
use Tests\TestCase;

class PaymentIntegrityTest extends TestCase
{
    use FinancialFixtures, RefreshDatabase;

    private function order(): Order
    {
        $author = $this->financialAuthor();
        $book = Book::create(['author_id' => $author->id, 'title' => 'Synthetic Sale', 'author' => $author->pen_name, 'price' => '100000.01', 'file_path' => 'a.pdf', 'is_published' => true]);
        $order = Order::create(['id' => 'TEST-ORDER', 'user_id' => User::factory()->create()->id, 'gross_amount' => '100000.01', 'status' => 'pending']);
        $order->items()->create(['book_id' => $book->id, 'price' => '100000.01']);

        return $order;
    }

    private function payload(Order $order): array
    {
        $p = ['order_id' => $order->id, 'gross_amount' => '100000.01', 'status_code' => '200', 'transaction_status' => 'settlement'];
        $p['signature_key'] = hash('sha512', $p['order_id'].$p['status_code'].$p['gross_amount'].'test-server-key');

        return $p;
    }

    public function test_missing_gateway_key_and_invalid_signatures_are_rejected(): void
    {
        Bus::fake();
        $order = $this->order();
        config(['midtrans.server_key' => '']);
        $this->postJson('/api/midtrans/webhook', $this->payload($order))->assertStatus(503);
        config(['midtrans.server_key' => 'test-server-key']);
        $this->postJson('/api/midtrans/webhook', ['order_id' => []])->assertUnprocessable();
        $p = $this->payload($order);
        $p['signature_key'] = str_repeat('0', 128);
        $this->postJson('/api/midtrans/webhook', $p)->assertForbidden();
        Bus::assertNothingDispatched();
    }

    public function test_valid_repeated_webhooks_are_safely_enqueued_without_pre_ack_cache(): void
    {
        Bus::fake();
        config(['midtrans.server_key' => 'test-server-key']);
        $order = $this->order();
        $this->postJson('/api/midtrans/webhook', $this->payload($order))->assertOk();
        $this->postJson('/api/midtrans/webhook', $this->payload($order))->assertOk();
        Bus::assertDispatchedTimes(ProcessMidtransWebhook::class, 2);
    }

    public function test_replayed_paid_job_produces_one_license_and_event(): void
    {
        Event::fake([OrderPaidEvent::class]);
        $order = $this->order();
        $job = new ProcessMidtransWebhook($this->payload($order));
        $job->handle();
        $job->handle();
        $this->artisan('payments:dispatch-events')->assertSuccessful();
        $this->assertDatabaseCount('book_licenses', 1);
        Event::assertDispatchedTimes(OrderPaidEvent::class, 1);
        $this->assertSame('success', $order->fresh()->status);
    }

    public function test_payment_amount_mismatch_rolls_back_and_does_not_grant_access(): void
    {
        Event::fake([OrderPaidEvent::class]);
        $order = $this->order();
        $p = $this->payload($order);
        $p['gross_amount'] = '1.00';
        try {
            (new ProcessMidtransWebhook($p))->handle();
            $this->fail('Mismatched amount accepted.');
        } catch (DomainException $e) {
            $this->assertSame('pending', $order->fresh()->status);
            $this->assertDatabaseCount('book_licenses', 0);
            Event::assertNotDispatched(OrderPaidEvent::class);
        }
    }

    public function test_card_capture_challenge_does_not_grant_access(): void
    {
        $order = $this->order();
        $p = $this->payload($order);
        $p['transaction_status'] = 'capture';
        $p['fraud_status'] = 'challenge';
        (new ProcessMidtransWebhook($p))->handle();
        $this->assertDatabaseCount('book_licenses', 0);
        $this->assertSame('pending', $order->fresh()->status);
    }

    public function test_failed_order_cannot_be_resurrected_by_late_callback(): void
    {
        $order = $this->order();
        $order->update(['status' => 'failed']);
        (new ProcessMidtransWebhook($this->payload($order)))->handle();
        $this->assertSame('failed', $order->fresh()->status);
        $this->assertDatabaseCount('book_licenses', 0);
    }

    public function test_repeated_royalty_events_use_exact_minor_units_and_one_entry(): void
    {
        $order = $this->order();
        $order->update(['status' => 'success']);
        $listener = new RecordAuthorRoyaltyListener;
        $listener->handle(new OrderPaidEvent($order));
        $listener->handle(new OrderPaidEvent($order));
        $this->assertDatabaseCount('royalty_ledgers', 1);
        $ledger = RoyaltyLedger::firstOrFail();
        $this->assertSame('70000.01', $ledger->author_earning);
        $this->assertSame('30000.00', $ledger->platform_earning);
        config(['features.royalty' => false]);
        $this->assertFalse($listener->shouldQueue(new OrderPaidEvent($order)));
    }

    public function test_unpaid_order_event_cannot_record_royalty(): void
    {
        $order = $this->order();
        (new RecordAuthorRoyaltyListener)->handle(new OrderPaidEvent($order));
        $this->assertDatabaseCount('royalty_ledgers', 0);
    }

    public function test_listener_discovery_has_no_duplicate_manual_registration(): void
    {
        $raw = app('events')->getRawListeners()[OrderPaidEvent::class] ?? [];
        $this->assertCount(2, $raw);
        $this->assertCount(2, array_unique($raw));
    }
}
