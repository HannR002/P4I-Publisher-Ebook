<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Mail\PurchaseConfirmationMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class SendPurchaseConfirmationListener implements ShouldQueue
{
    use InteractsWithQueue;

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
            $event->order->loadMissing(['user', 'items.book']);
            Mail::to($event->order->user->email)->send(new PurchaseConfirmationMail($event->order));
        } catch (\Exception $e) {
            Log::error('[SendPurchaseConfirmationListener] Gagal mengirim surel konfirmasi.', [
                'order_id' => $event->order->id,
                'error'    => $e->getMessage()
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
            'error'    => $exception->getMessage()
        ]);
    }
}
