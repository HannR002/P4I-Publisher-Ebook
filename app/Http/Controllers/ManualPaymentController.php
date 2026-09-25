<?php

namespace App\Http\Controllers;

use App\Models\LibraryItem;
use App\Models\ManualOrder;
use App\Models\PaymentMethod;
use App\Models\PaymentSubmission;
use App\Services\AnalyticsRecorder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ManualPaymentController extends Controller
{
    public function store(Request $request, LibraryItem $libraryItem, AnalyticsRecorder $analytics)
    {
        abort_unless(in_array($libraryItem->access_policy, ['manual_purchase', 'physical_only'], true), 422);
        abort_if($libraryItem->accessGrants()->where('user_id', $request->user()->id)->exists(), 422, 'Anda sudah memiliki akses.');

        $order = DB::transaction(function () use ($request, $libraryItem) {
            $price = $libraryItem->price;
            $order = ManualOrder::create([
                'user_id' => $request->user()->id,
                'order_type' => $libraryItem->access_policy === 'physical_only' ? 'printed_book' : 'digital_publication',
                'subtotal' => $price, 'shipping_cost' => 0, 'total' => $price,
                'status' => 'awaiting_payment',
            ]);
            $order->items()->create([
                'item_type' => LibraryItem::class, 'item_id' => $libraryItem->id,
                'description' => $libraryItem->title, 'quantity' => 1,
                'unit_price' => $price, 'subtotal' => $price,
            ]);
            return $order;
        });

        $analytics->record('payment_started', $request, $libraryItem);
        return redirect()->route('manual-orders.show', $order);
    }

    public function show(Request $request, ManualOrder $manualOrder)
    {
        abort_unless($manualOrder->user_id === $request->user()->id, 403);
        $manualOrder->load(['items', 'paymentSubmissions.paymentMethod']);
        return view('manual-orders.show', [
            'order' => $manualOrder,
            'paymentMethods' => PaymentMethod::where('is_active', true)->orderBy('sort_order')->get(),
        ]);
    }

    public function submitProof(Request $request, ManualOrder $manualOrder, AnalyticsRecorder $analytics)
    {
        abort_unless($manualOrder->user_id === $request->user()->id, 403);
        abort_unless(in_array($manualOrder->status, ['awaiting_payment', 'rejected'], true), 422);
        $validated = $request->validate([
            'payment_method_id' => ['required', 'exists:payment_methods,id'],
            'amount' => ['required', 'numeric', 'min:1'],
            'proof' => ['required', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:5120'],
        ]);
        if (round((float) $validated['amount'], 2) !== round((float) $manualOrder->total, 2)) {
            return back()->withErrors(['amount' => 'Nominal bukti pembayaran harus sama dengan total pesanan.']);
        }
        abort_unless(PaymentMethod::whereKey($validated['payment_method_id'])->where('is_active', true)->exists(), 422);
        $path = $request->file('proof')->store('payment-proofs', 'local');
        $submission = PaymentSubmission::create([
            'manual_order_id' => $manualOrder->id,
            'payment_method_id' => $validated['payment_method_id'],
            'amount' => $validated['amount'], 'proof_path' => $path,
            'status' => 'submitted', 'submitted_at' => now(),
        ]);
        $manualOrder->update(['status' => 'payment_submitted']);
        $item = LibraryItem::find($manualOrder->items()->where('item_type', LibraryItem::class)->value('item_id'));
        $analytics->record('payment_submitted', $request, $item);

        return back()->with('success', 'Bukti pembayaran tersimpan dan menunggu verifikasi admin.');
    }
}
