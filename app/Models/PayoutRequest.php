<?php

namespace App\Models;

use App\Casts\EncryptedFinancialString;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class PayoutRequest extends Model
{
    public $incrementing = false;

    protected $keyType = 'string';

    protected $fillable = [
        'author_id',
        'idempotency_key',
        'amount',
        'bank_name_snapshot',
        'bank_account_snapshot',
        'bank_holder_name_snapshot',
        'status',
        'transfer_proof_path',
        'reference_number',
        'admin_notes',
        'processed_by',
        'processed_at',
    ];

    protected $hidden = ['bank_name_snapshot', 'bank_account_snapshot', 'bank_holder_name_snapshot'];

    protected $casts = [
        'bank_name_snapshot' => EncryptedFinancialString::class,
        'bank_account_snapshot' => EncryptedFinancialString::class,
        'bank_holder_name_snapshot' => EncryptedFinancialString::class,
        'amount' => 'decimal:2',
        'processed_at' => 'datetime',
    ];

    protected static function booted()
    {
        static::creating(function ($model) {
            if (empty($model->id)) {
                $model->id = (string) Str::ulid();
            }
        });
    }

    public function allocations()
    {
        return $this->hasMany(PayoutAllocation::class);
    }

    public function audits()
    {
        return $this->hasMany(PayoutAudit::class);
    }

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function processor()
    {
        return $this->belongsTo(User::class, 'processed_by');
    }
}
