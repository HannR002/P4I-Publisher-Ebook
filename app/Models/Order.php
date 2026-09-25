<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Order extends Model
{
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'user_id',
        'gross_amount',
        'status',
        'payment_type',
        'snap_token',
    ];

    protected $casts = [
        'gross_amount' => 'decimal:2',
        'snap_token'   => 'encrypted',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function items()
    {
        return $this->hasMany(OrderItem::class);
    }
}
