<?php

namespace Tests\Feature;

use App\Events\OrderPaidEvent;
use App\Mail\PurchaseConfirmationMail;
use App\Models\Book;
use App\Models\Order;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Illuminate\Support\Facades\Mail;
use Tests\TestCase;

class PurchaseNotificationTest extends TestCase
{
    use RefreshDatabase;

    public function test_order_paid_event_triggered_on_midtrans_webhook_settlement()
    {
        Event::fake([OrderPaidEvent::class]);

        $user = User::factory()->create();
        $book = Book::create([
            'title'        => 'Test Book',
            'author'       => 'QA Author',
            'description'  => 'Test description',
            'price'        => 50000,
            'file_path'    => 'dummy.pdf',
            'is_published' => true,
        ]);

        $order = Order::create([
            'id'           => 'ORD-TEST-123',
            'user_id'      => $user->id,
            'gross_amount' => 50000,
            'status'       => 'pending',
            'payment_type' => 'bank_transfer',
        ]);

        $order->items()->create([
            'book_id' => $book->id,
            'price'   => 50000,
        ]);

        $payload = [
            'order_id'           => $order->id,
            'transaction_status' => 'settlement',
            'payment_type'       => 'bank_transfer',
        ];

        $job = new \App\Jobs\ProcessMidtransWebhook($payload);
        $job->handle();

        Event::assertDispatched(OrderPaidEvent::class, function ($e) use ($order) {
            return $e->order->id === $order->id;
        });
    }

    public function test_order_paid_event_triggered_on_free_book_checkout()
    {
        Event::fake([OrderPaidEvent::class]);

        $user = User::factory()->create();
        $book = Book::create([
            'title'        => 'Free Book',
            'author'       => 'QA Author',
            'description'  => 'Test description',
            'price'        => 0,
            'file_path'    => 'dummy.pdf',
            'is_published' => true,
        ]);

        $response = $this->actingAs($user)->post(route('checkout.store'), [
            'book_ids' => [$book->id],
        ]);

        $response->assertRedirect(route('my-library'));

        Event::assertDispatched(OrderPaidEvent::class);
    }

    public function test_mailable_sent_to_correct_email_with_order_data()
    {
        Mail::fake();

        $user = User::factory()->create(['email' => 'buyer@example.com']);
        $book = Book::create([
            'title'        => 'Test Book',
            'author'       => 'QA Author',
            'description'  => 'Test description',
            'price'        => 50000,
            'file_path'    => 'dummy.pdf',
            'is_published' => true,
        ]);

        $order = Order::create([
            'id'           => 'ORD-MAIL-123',
            'user_id'      => $user->id,
            'gross_amount' => 50000,
            'status'       => 'success',
            'payment_type' => 'bank_transfer',
        ]);

        $order->items()->create([
            'book_id' => $book->id,
            'price'   => 50000,
        ]);

        $event = new OrderPaidEvent($order);
        $listener = new \App\Listeners\SendPurchaseConfirmationListener();
        $listener->handle($event);

        Mail::assertSent(PurchaseConfirmationMail::class, function ($mail) use ($order) {
            return $mail->hasTo('buyer@example.com');
        });
    }
}
