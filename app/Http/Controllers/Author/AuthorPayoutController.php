<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StorePayoutRequest;
use App\Models\PayoutRequest;
use App\Services\PayoutService;
use App\Services\RoyaltyBalanceService;
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

        $balances = app(RoyaltyBalanceService::class)->balances($author->id);
        $availableBalance = $balances['available'];
        $pendingBalance = $balances['pending'];
        $withdrawnBalance = $balances['withdrawn'];

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
            $payoutService->requestPayout($request->user()->authorProfile, $request->input('amount'), $request->input('idempotency_key'));

            return back()->with('success', 'Permohonan penarikan dana berhasil diajukan dan sedang diproses.');
        } catch (DomainException $e) {
            return back()->withErrors(['amount' => $e->getMessage()]);
        }
    }
}
