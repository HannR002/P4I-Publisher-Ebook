<?php

namespace App\Services;

use App\Events\OrderPaidEvent;
use App\Models\Order;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class OrderPaidOutbox
{
    public function record(Order $order): void
    {
        // Must be called inside the order/license transaction.
        DB::table('order_paid_outbox')->insertOrIgnore(['order_id' => $order->id, 'created_at' => now(), 'updated_at' => now()]);
        DB::afterCommit(fn () => $this->dispatch($order->id));
    }

    public function dispatch(string $orderId): bool
    {
        try {
            // This short transaction owns a durable row lock. For database
            // queues, dispatch jobs and the marker commit atomically. Sync
            // listeners execute here and are idempotent on repeated delivery.
            return DB::transaction(function () use ($orderId) {
                $row = DB::table('order_paid_outbox')->where('order_id', $orderId)->lockForUpdate()->first();
                if (! $row || $row->dispatched_at !== null) {
                    return true;
                }
                $order = Order::findOrFail($orderId);
                if ($order->status !== 'success') {
                    return false;
                }
                // Outbox is itself after the sale commit. Override listener
                // queue after_commit for atomic database-queue publication.
                event(new OrderPaidEvent($order));
                DB::table('order_paid_outbox')->where('order_id', $orderId)->update(['dispatched_at' => now(), 'updated_at' => now()]);

                return true;
            });
        } catch (\Throwable $e) {
            Log::error('Paid-order event publication failed; durable outbox retained.', ['order_id' => $orderId, 'exception_class' => $e::class]);

            return false;
        }
    }
}
