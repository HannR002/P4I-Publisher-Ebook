<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Str;
use App\Models\Order;
use App\Models\BookLicense;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        abort_unless(config('features.midtrans'), 404, 'Midtrans tidak aktif.');

        Log::channel('single')->info('[Midtrans Webhook] ▶ Webhook diterima', [
            'order_id'           => $request->order_id,
            'transaction_status' => $request->transaction_status,
            'payment_type'       => $request->payment_type,
        ]);

        $serverKey   = config('midtrans.server_key');
        $orderId     = $request->order_id;
        $statusCode  = $request->status_code;
        $grossAmount = $request->gross_amount;
        $incoming    = $request->signature_key;
        $expected    = hash('sha512', $orderId . $statusCode . $grossAmount . $serverKey);

        if ($incoming !== $expected) {
            Log::channel('single')->warning('[Midtrans Webhook] ✗ Signature MISMATCH', [
                'order_id' => $orderId,
            ]);
            return response()->json(['message' => 'Invalid signature'], 403);
        }

        // BUG-04 FIXED: Webhook Replay Protection using Cache (fingerprint valid for 5 mins)
        $webhookFingerprint = hash('sha256', $request->getContent());
        if (\Illuminate\Support\Facades\Cache::has("webhook:{$webhookFingerprint}")) {
            Log::channel('single')->info('[Midtrans Webhook] ↩ Replay protection: duplicate webhook ignored', ['order_id' => $orderId]);
            return response()->json(['message' => 'Duplicate webhook ignored']);
        }
        \Illuminate\Support\Facades\Cache::put("webhook:{$webhookFingerprint}", true, now()->addMinutes(5));

        Log::channel('single')->info('[Midtrans Webhook] ✓ Signature valid', [
            'order_id' => $orderId,
        ]);

        \App\Jobs\ProcessMidtransWebhook::dispatch($request->all());

        return response()->json(['message' => 'Webhook received and queued'], 200);
    }
}
