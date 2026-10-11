<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Mail\PurchaseConfirmationMail;
use App\Models\Order;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseConfirmationListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = false;

    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(OrderPaidEvent $event): void
    {
        try {
            DB::transaction(function () use ($event) {
                $order = Order::whereKey($event->order->id)->lockForUpdate()->firstOrFail();
                if ($order->status !== 'success' || $order->confirmation_sent_at !== null) {
                    return;
                }
                $order->loadMissing(['user', 'items.book']);
                Mail::to($order->user->email)->send(new PurchaseConfirmationMail($order));
                $order->forceFill(['confirmation_sent_at' => now()])->save();
            });
        } catch (\Exception $e) {
            Log::error('[SendPurchaseConfirmationListener] Gagal mengirim surel konfirmasi.', [
                'order_id' => $event->order->id,
                'exception_class' => $e::class,
            ]);
            throw $e;
        }
    }

    /**
     * Handle a job failure.
     */
    public function failed(OrderPaidEvent $event, \Throwable $exception): void
    {
        Log::error('[SendPurchaseConfirmationListener] Job antrean gagal secara permanen.', [
            'order_id' => $event->order->id,
            'exception_class' => $exception::class,
        ]);
    }
}
