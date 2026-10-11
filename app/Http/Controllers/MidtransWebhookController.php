<?php

namespace App\Http\Controllers;

use App\Jobs\ProcessMidtransWebhook;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class MidtransWebhookController extends Controller
{
    public function handle(Request $request)
    {
        abort_unless(config('features.midtrans'), 404);
        $serverKey = config('midtrans.server_key');
        abort_unless(is_string($serverKey) && $serverKey !== '', 503, 'Payment gateway is not configured.');
        $payload = $request->validate([
            'order_id' => ['required', 'string', 'max:255'],
            'status_code' => ['required', 'string', 'regex:/\A[0-9]{3}\z/'],
            'gross_amount' => ['required', 'string', 'regex:/\A(?:0|[1-9][0-9]{0,9})(?:\.[0-9]{1,2})?\z/'],
            'signature_key' => ['required', 'string', 'regex:/\A[a-fA-F0-9]{128}\z/'],
            'transaction_status' => ['required', Rule::in(['capture', 'settlement', 'pending', 'cancel', 'deny', 'expire'])],
            'fraud_status' => ['nullable', Rule::in(['accept', 'challenge', 'deny'])],
            'payment_type' => ['nullable', 'string', 'max:50'],
        ]);
        $expected = hash('sha512', $payload['order_id'].$payload['status_code'].$payload['gross_amount'].$serverKey);
        abort_unless(hash_equals($expected, strtolower($payload['signature_key'])), 403, 'Invalid signature');

        // Do not cache an acknowledgement before successful enqueueing. Durable
        // idempotency and serialization are enforced by the locked order row.
        unset($payload['signature_key']);
        ProcessMidtransWebhook::dispatch($payload);

        return response()->json(['message' => 'Webhook received and queued']);
    }
}
