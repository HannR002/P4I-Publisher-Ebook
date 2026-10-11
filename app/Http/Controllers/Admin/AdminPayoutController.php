<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class AdminPayoutController extends Controller
{
    /**
     * Display a listing of payout requests.
     */
    public function index(Request $request)
    {
        $status = $request->query('status');

        $query = PayoutRequest::with(['author.user', 'processor'])->orderBy('created_at', 'desc');

        if ($status && in_array($status, ['requested', 'processing', 'completed', 'rejected'])) {
            $query->where('status', $status);
        }

        $payouts = $query->paginate(15);

        return view('admin.payouts.index', compact('payouts', 'status'));
    }

    /**
     * Display the specified payout request.
     */
    public function show(PayoutRequest $payout)
    {
        $payout->load(['author.user', 'processor']);

        $allocations = $payout->allocations()->with(['royaltyLedger.orderItem', 'royaltyLedger.book'])->get();

        return view('admin.payouts.show', compact('payout', 'allocations'));
    }

    /**
     * Mark the payout request as complete.
     */
    public function complete(Request $request, PayoutRequest $payout, PayoutService $payoutService)
    {
        $request->validate([
            'reference_number' => ['required', 'string', 'max:100'],
            'transfer_proof' => ['nullable', 'file', 'mimes:jpg,jpeg,png,pdf', 'max:3072'],
        ]);

        $proofPath = null;
        if ($request->hasFile('transfer_proof')) {
            $proofPath = $request->file('transfer_proof')->store('private/transfer_proofs', 'local');
        }

        try {
            $payoutService->completePayout($payout, $request->reference_number, $proofPath, $request->user()->id);
        } catch (\DomainException $e) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }

            return back()->withErrors(['payout' => $e->getMessage()]);
        } catch (\Throwable $e) {
            if ($proofPath) {
                Storage::disk('local')->delete($proofPath);
            }
            throw $e;
        }

        return back()->with('success', 'Penarikan dana berhasil diselesaikan.');
    }

    /**
     * Reject the payout request.
     */
    public function reject(Request $request, PayoutRequest $payout, PayoutService $payoutService)
    {
        $request->validate([
            'admin_notes' => ['required', 'string', 'min:5', 'max:1000'],
        ]);

        try {
            $payoutService->rejectPayout($payout, $request->admin_notes, $request->user()->id);
        } catch (\DomainException $e) {
            return back()->withErrors(['payout' => $e->getMessage()]);
        }

        return back()->with('success', 'Penarikan dana berhasil ditolak.');
    }
}
