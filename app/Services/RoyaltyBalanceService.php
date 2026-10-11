<?php

namespace App\Services;

use App\Models\PayoutAllocation;
use App\Models\RoyaltyLedger;
use App\Support\Money;

class RoyaltyBalanceService
{
    public function balances(int $authorId): array
    {
        $earned = RoyaltyLedger::where('author_id', $authorId)->get(['author_earning'])->sum(fn ($row) => Money::minor($row->author_earning));
        $reserved = $this->allocated($authorId, ['requested', 'processing']);
        $withdrawn = $this->allocated($authorId, ['completed']);

        return [
            'available' => Money::decimal($earned - $reserved - $withdrawn),
            'pending' => Money::decimal($reserved),
            'withdrawn' => Money::decimal($withdrawn),
        ];
    }

    private function allocated(int $authorId, array $states): int
    {
        return PayoutAllocation::whereHas('royaltyLedger', fn ($q) => $q->where('author_id', $authorId))
            ->whereHas('payoutRequest', fn ($q) => $q->whereIn('status', $states))
            ->get(['amount'])->sum(fn ($row) => Money::minor($row->amount));
    }
}
