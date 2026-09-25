<?php

namespace App\Listeners;

use App\Events\OrderPaidEvent;
use App\Models\RoyaltyLedger;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Support\Facades\Log;

class RecordAuthorRoyaltyListener implements ShouldQueue
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
        if (! config('features.royalty')) {
            return;
        }

        try {
            $event->order->loadMissing('items.book');

            foreach ($event->order->items as $item) {
                $book = $item->book;

                if ($book && $book->author_id) {
                    $grossSale = $item->price;
                    $authorPercentage = 70.00;
                    $authorEarning = round(($grossSale * $authorPercentage) / 100, 2);
                    $platformEarning = $grossSale - $authorEarning;

                    RoyaltyLedger::firstOrCreate(
                        ['order_item_id' => $item->id],
                        [
                            'author_id' => $book->author_id,
                            'book_id' => $book->id,
                            'gross_sale' => $grossSale,
                            'author_percentage' => $authorPercentage,
                            'author_earning' => $authorEarning,
                            'platform_earning' => $platformEarning,
                            'status' => 'available',
                        ]
                    );
                    
                    Log::info('[ROYALTY_RECORDED] Royalty logged for author', [
                        'author_id' => $book->author_id,
                        'order_item_id' => $item->id,
                        'earning' => $authorEarning
                    ]);
                }
            }
        } catch (\Exception $e) {
            Log::error('[ROYALTY_ERROR] Failed to record royalty', [
                'order_id' => $event->order->id ?? null,
                'error' => $e->getMessage()
            ]);
            throw $e;
        }
    }
}
