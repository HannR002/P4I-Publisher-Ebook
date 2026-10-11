<?php

namespace App\Models;

use DomainException;
use Illuminate\Database\Eloquent\Model;

class PayoutAudit extends Model
{
    public $timestamps = false;

    protected $fillable = ['payout_request_id', 'action', 'actor_id', 'occurred_at'];

    protected $casts = ['occurred_at' => 'datetime'];

    protected static function booted(): void
    {
        static::updating(fn () => throw new DomainException('Audit payout tidak dapat diubah.'));
        static::deleting(fn () => throw new DomainException('Audit payout tidak dapat dihapus.'));
    }
}
