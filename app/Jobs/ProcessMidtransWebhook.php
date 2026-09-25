<?php

namespace App\Jobs;

use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\BookLicense;

class ProcessMidtransWebhook implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    protected $payload;

    /**
     * Create a new job instance.
     */
    public function __construct(array $payload)
    {
        $this->payload = $payload;
    }

    /**
     * Execute the job.
     */
    public function handle(): void
    {
        if (! config('features.midtrans')) {
            Log::notice('[Midtrans Webhook Job] Skipped because Midtrans is disabled.');
            return;
        }

        $orderId           = $this->payload['order_id'];
        $transactionStatus = $this->payload['transaction_status'];
        $paymentType       = $this->payload['payment_type'] ?? null;

        DB::beginTransaction();
        try {
            $order = Order::with('items')->where('id', $orderId)->lockForUpdate()->first();

            if (!$order) {
                DB::rollBack();
                if (str_starts_with($orderId, 'payment_notif_test_')) {
                    Log::channel('single')->info('[Midtrans Webhook Job] ✓ Test notification dari dashboard Midtrans — OK', [
                        'order_id' => $orderId,
                    ]);
                    return;
                }
                Log::channel('single')->error('[Midtrans Webhook Job] ✗ Order tidak ditemukan', ['order_id' => $orderId]);
                return;
            }

            if ($order->status === 'success') {
                DB::rollBack();
                Log::channel('single')->info('[Midtrans Webhook Job] ↩ Idempotency: order sudah success, skip', ['order_id' => $orderId]);
                return;
            }

            if ($transactionStatus === 'capture' || $transactionStatus === 'settlement') {
                $order->status = 'success';

                foreach ($order->items as $item) {
                    $lic = BookLicense::firstOrCreate(
                        [
                            'user_id' => $order->user_id,
                            'book_id' => $item->book_id,
                        ],
                        [
                            'order_id'    => $order->id,
                            'license_key' => (string) Str::uuid(),
                            'status'      => 'active',
                        ]
                    );

                    Log::channel('single')->info('[Midtrans Webhook Job] BookLicense', [
                        'book_id'              => $item->book_id,
                        'license_key'          => $lic->license_key,
                        'was_recently_created' => $lic->wasRecentlyCreated,
                        'status'               => $lic->status,
                    ]);
                }
                
                event(new \App\Events\OrderPaidEvent($order));
            } elseif (in_array($transactionStatus, ['cancel', 'deny'])) {
                $order->status = 'failed';
            } elseif ($transactionStatus === 'expire') {
                $order->status = 'expired';
            } elseif ($transactionStatus === 'pending') {
                $order->status = 'pending';
            }

            if ($paymentType) {
                $order->payment_type = $paymentType;
            }
            $order->save();

            DB::commit();

            Log::channel('single')->info('[Midtrans Webhook Job] ✓ Order selesai diproses', [
                'order_id'     => $order->id,
                'status_baru'  => $order->status,
                'payment_type' => $order->payment_type,
            ]);

        } catch (\Exception $e) {
            DB::rollBack();
            Log::channel('single')->error('[Midtrans Webhook Job] ✗ Exception', [
                'order_id' => $orderId,
                'error'    => $e->getMessage(),
                'trace'    => $e->getTraceAsString(),
            ]);
            throw $e;
        }
    }
}
