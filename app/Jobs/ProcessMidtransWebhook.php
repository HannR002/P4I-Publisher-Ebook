<?php

namespace App\Jobs;

use App\Models\BookLicense;
use App\Models\Order;
use App\Services\OrderPaidOutbox;
use App\Support\Money;
use DomainException;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProcessMidtransWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 3;

    public function __construct(protected array $payload) {}

    public function handle(): void
    {
        if (! config('features.midtrans')) {
            return;
        }
        DB::transaction(function () {
            $order = Order::whereKey($this->payload['order_id'])->lockForUpdate()->first();
            if (! $order) {
                return;
            }
            if (! isset($this->payload['gross_amount']) || Money::minor($this->payload['gross_amount']) !== Money::minor($order->gross_amount)) {
                throw new DomainException('Payment amount does not match the order.');
            }
            if ($order->status !== 'pending') {
                return;
            }
            $status = $this->payload['transaction_status'];
            $fraud = $this->payload['fraud_status'] ?? null;
            $paid = ($status === 'settlement' && in_array($fraud, [null, 'accept'], true))
                || ($status === 'capture' && $fraud === 'accept');
            if ($paid && ($this->payload['status_code'] ?? null) !== '200') {
                throw new DomainException('Payment success status is inconsistent.');
            }
            if ($paid) {
                $order->update(['status' => 'success', 'payment_type' => $this->payload['payment_type'] ?? null]);
                foreach ($order->items()->get() as $item) {
                    BookLicense::firstOrCreate([
                        'user_id' => $order->user_id, 'book_id' => $item->book_id,
                    ], [
                        'order_id' => $order->id, 'license_key' => (string) Str::uuid(), 'status' => 'active',
                    ]);
                }
                // Persist delivery inside the sale transaction; publish after commit.
                app(OrderPaidOutbox::class)->record($order);
            } elseif (in_array($status, ['cancel', 'deny', 'expire'], true)) {
                $order->update(['status' => $status === 'expire' ? 'expired' : 'failed']);
            }
        }, 3);
    }
}
