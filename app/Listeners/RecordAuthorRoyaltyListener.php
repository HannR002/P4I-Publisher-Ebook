<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Models\Order;
use App\Models\RoyaltyLedger;
use App\Support\Money;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\DB;

class RecordAuthorRoyaltyListener implements ShouldQueue
{
    use InteractsWithQueue;

    public bool $afterCommit = false;

    public function shouldQueue(OrderPaidEvent $event): bool
    {
        return (bool) config('features.royalty');
    }

    public function handle(OrderPaidEvent $event): void
    {
        if (! config('features.royalty')) {
            return;
        }
        DB::transaction(function () use ($event) {
            $order = Order::whereKey($event->order->id)->lockForUpdate()->firstOrFail();
            if ($order->status !== 'success') {
                return;
            }
            foreach ($order->items()->with('book')->get() as $item) {
                if (! $item->book?->author_id) {
                    continue;
                }
                $gross = Money::minor((string) $item->price);
                $author = intdiv($gross * 70 + 50, 100); // Half-up in minor units.
                RoyaltyLedger::firstOrCreate(['order_item_id' => $item->id], [
                    'author_id' => $item->book->author_id,
                    'book_id' => $item->book_id,
                    'gross_sale' => Money::decimal($gross),
                    'author_percentage' => '70.00',
                    'author_earning' => Money::decimal($author),
                    'platform_earning' => Money::decimal($gross - $author),
                    'status' => 'available',
                ]);
            }
        }, 3);
    }
}
