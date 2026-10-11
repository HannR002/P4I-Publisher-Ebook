<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class PayoutAllocation extends Model
{
    protected $fillable = ['payout_request_id', 'royalty_ledger_id', 'amount'];

    protected $casts = ['amount' => 'decimal:2'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new DomainException('Alokasi payout tidak dapat diubah.'));
        static::deleting(fn () => throw new DomainException('Alokasi payout tidak dapat dihapus.'));
    }

    public function payoutRequest()
    {
        return $this->belongsTo(PayoutRequest::class);
    }

    public function royaltyLedger()
    {
        return $this->belongsTo(RoyaltyLedger::class);
    }
}
