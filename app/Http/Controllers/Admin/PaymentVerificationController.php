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
    public function index(Request $request)
    {
        $status = $request->get('status', 'submitted');

        $query = PaymentSubmission::with(['order.user', 'order.items', 'paymentMethod']);

        if ($status && $status !== 'all') {
            $query->where('status', $status);
        }

        if ($search = $request->get('search')) {
            $query->whereHas('order', function($q) use ($search) {
                $q->where('order_number', 'like', "%{$search}%")
                  ->orWhereHas('user', function($uq) use ($search) {
                      $uq->where('name', 'like', "%{$search}%")->orWhere('email', 'like', "%{$search}%");
                  });
            });
        }

        $submissions = $query->latest()->paginate(20)->withQueryString();

        return view('admin.payments.index', compact('submissions', 'status', 'search'));
    }

    public function proof(PaymentSubmission $paymentSubmission)
    {
        abort_unless(Storage::disk('local')->exists($paymentSubmission->proof_path), 404);
        return response()->file(Storage::disk('local')->path($paymentSubmission->proof_path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function show(PaymentSubmission $paymentSubmission)
    {
        $paymentSubmission->load(['order.user', 'order.items', 'paymentMethod', 'verifier']);
        return view('admin.payments.show', compact('paymentSubmission'));
    }

    public function verify(Request $request, PaymentSubmission $paymentSubmission)
    {
        $alreadyVerified = false;
        DB::transaction(function () use ($request, $paymentSubmission, &$alreadyVerified): void {
            $submission = PaymentSubmission::whereKey($paymentSubmission->id)->lockForUpdate()->firstOrFail();
            if ($submission->status === 'verified') {
                $alreadyVerified = true;
                return;
            }
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
        if ($alreadyVerified) {
            return back()->with('success', 'Pembayaran sudah pernah diverifikasi.');
        }
        return back()->with('success', 'Pembayaran diverifikasi dan akses digital diberikan bila berlaku.');
    }

    public function reject(Request $request, PaymentSubmission $paymentSubmission)
    {
        $data = $request->validate(['rejection_reason' => ['required', 'string', 'max:1000']]);
        $alreadyRejected = false;
        DB::transaction(function () use ($request, $paymentSubmission, $data, &$alreadyRejected): void {
            $submission = PaymentSubmission::whereKey($paymentSubmission->id)->lockForUpdate()->firstOrFail();
            if ($submission->status === 'rejected') {
                $alreadyRejected = true;
                return;
            }
            abort_unless($submission->status === 'submitted', 409, 'Bukti pembayaran sudah diproses.');
            $submission->update(['status' => 'rejected', 'verified_by' => $request->user()->id, 'rejection_reason' => $data['rejection_reason']]);
            $submission->order()->update(['status' => 'rejected']);
        });
        if ($alreadyRejected) {
            return back()->with('success', 'Pembayaran sudah pernah ditolak.');
        }
        return back()->with('success', 'Pembayaran ditolak.');
    }
}
