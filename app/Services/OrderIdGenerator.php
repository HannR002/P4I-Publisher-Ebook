<?php

namespace App\Services;

use Illuminate\Support\Str;
use App\Models\Order;

class OrderIdGenerator
{
    /**
     * Generate a unique Order ID in the format INV-{YYYYMMDD}-{RANDOM_HEX}.
     *
     * The generated ID is validated to be unique in the orders table
     * before being returned. This is important because it's also used
     * as the Midtrans order_id, which rejects duplicates.
     *
     * @return string
     */
    public static function generate(): string
    {
        $maxAttempts = 10;
        for ($i = 0; $i < $maxAttempts; $i++) {
            $orderId = (string) Str::ulid();
            if (!Order::where('id', $orderId)->exists()) {
                return $orderId;
            }
        }
        
        throw new \RuntimeException('Failed to generate unique order ID.');
    }
}
