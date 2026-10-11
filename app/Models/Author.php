<?php

namespace App\Models;

use App\Casts\EncryptedFinancialString;
use App\Services\RoyaltyBalanceService;
use Illuminate\Database\Eloquent\Model;

class Author extends Model
{
    protected $fillable = [
        'user_id',
        'pen_name',
        'bio',
        'id_card_number',
        'id_card_path',
        'bank_name',
        'bank_account',
        'bank_holder_name',
        'kyc_status',
        'rejection_reason',
        'verified_at',
    ];

    protected $hidden = ['bank_name', 'bank_account', 'bank_holder_name', 'id_card_number', 'id_card_path'];

    protected $casts = [
        'bank_name' => EncryptedFinancialString::class,
        'bank_account' => EncryptedFinancialString::class,
        'bank_holder_name' => EncryptedFinancialString::class,
        'id_card_number' => 'encrypted',
        'verified_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function books()
    {
        return $this->hasMany(Book::class);
    }

    public function submissions()
    {
        return $this->hasMany(BookSubmission::class);
    }

    public function royaltyLedgers()
    {
        return $this->hasMany(RoyaltyLedger::class);
    }

    public function payoutRequests()
    {
        return $this->hasMany(PayoutRequest::class);
    }

    public function isVerified(): bool
    {
        return $this->kyc_status === 'verified';
    }

    public function getAvailableBalanceAttribute()
    {
        return app(RoyaltyBalanceService::class)->balances($this->id)['available'];
    }
}
