<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\LibraryAccessGrant;
use App\Models\LibraryItem;
use App\Models\PaymentSubmission;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;

class PaymentVerificationController extends Controller
{
    public function index()
    {
        return view('admin.payments.index', ['submissions' => PaymentSubmission::with(['order.user', 'paymentMethod'])->latest()->paginate(20)]);
    }

    public function proof(PaymentSubmission $paymentSubmission)
    {
        abort_unless(Storage::disk('local')->exists($paymentSubmission->proof_path), 404);
        return response()->file(Storage::disk('local')->path($paymentSubmission->proof_path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function verify(Request $request, PaymentSubmission $paymentSubmission)
    {
        DB::transaction(function () use ($request, $paymentSubmission): void {
            $submission = PaymentSubmission::whereKey($paymentSubmission->id)->lockForUpdate()->firstOrFail();
            abort_unless($submission->status === 'submitted', 409, 'Bukti pembayaran sudah diproses.');
            $submission->update(['status' => 'verified', 'verified_at' => now(), 'verified_by' => $request->user()->id, 'rejection_reason' => null]);
            $order = $submission->order()->with('items')->lockForUpdate()->first();
            $order->update(['status' => $order->order_type === 'digital_publication' ? 'verified' : 'processing']);

            if ($order->order_type === 'digital_publication') {
                foreach ($order->items->where('item_type', LibraryItem::class) as $item) {
                    LibraryAccessGrant::updateOrCreate(
                        ['user_id' => $order->user_id, 'library_item_id' => $item->item_id],
                        ['source_type' => 'manual_order', 'source_id' => $order->id, 'granted_at' => now(), 'expires_at' => null]
                    );
                }
            }
        });
        return back()->with('success', 'Pembayaran diverifikasi dan akses digital diberikan bila berlaku.');
    }

    public function reject(Request $request, PaymentSubmission $paymentSubmission)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        DB::transaction(function () use ($request, $paymentSubmission, $data): void {
            $submission = PaymentSubmission::whereKey($paymentSubmission->id)->lockForUpdate()->firstOrFail();
            abort_unless($submission->status === 'submitted', 409, 'Bukti pembayaran sudah diproses.');
            $submission->update(['status' => 'rejected', 'verified_by' => $request->user()->id, 'rejection_reason' => $data['rejection_reason']]);
            $submission->order()->update(['status' => 'rejected']);
        });
        return back()->with('success', 'Pembayaran ditolak.');
    }
}
