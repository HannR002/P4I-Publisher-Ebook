<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StorePayoutRequest;
use App\Models\PayoutRequest;
use App\Models\RoyaltyLedger;
use App\Services\PayoutService;
use DomainException;
use Illuminate\Http\Request;

class AuthorPayoutController extends Controller
{
    /**
     * Display a listing of payouts and the payout request form.
     */
    public function index(Request $request)
    {
        $author = $request->user()->authorProfile;

        $availableBalance = RoyaltyLedger::where('author_id', $author->id)
            ->where('status', 'available')
            ->sum('author_earning');

        $pendingBalance = RoyaltyLedger::where('author_id', $author->id)
            ->where('status', 'pending')
            ->sum('author_earning');

        $withdrawnBalance = RoyaltyLedger::where('author_id', $author->id)
            ->where('status', 'withdrawn')
            ->sum('author_earning');

        $payouts = PayoutRequest::where('author_id', $author->id)
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('author.payouts.index', compact(
            'availableBalance',
            'pendingBalance',
            'withdrawnBalance',
            'payouts',
            'author'
        ));
    }

    /**
     * Store a newly created payout request in storage.
     */
    public function store(StorePayoutRequest $request, PayoutService $payoutService)
    {
        try {
            $payoutService->requestPayout($request->user()->authorProfile, $request->amount);
            
            return back()->with('success', 'Permohonan penarikan dana berhasil diajukan dan sedang diproses.');
        } catch (DomainException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
