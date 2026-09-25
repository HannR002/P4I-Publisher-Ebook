<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookLicense extends Model
{
    protected $fillable = [
        'user_id',
        'book_id',
        'order_id',
        'license_key',
        'status',
        'valid_until',
        'revocation_reason',
    ];

    protected $casts = [
        'valid_until' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function order()
    {
        return $this->belongsTo(Order::class);
    }
}
