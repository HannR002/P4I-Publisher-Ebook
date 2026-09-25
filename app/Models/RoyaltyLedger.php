<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RoyaltyLedger extends Model
{
    protected $fillable = [
        'author_id',
        'order_item_id',
        'book_id',
        'payout_request_id',
        'gross_sale',
        'author_percentage',
        'author_earning',
        'platform_earning',
        'status',
    ];

    protected $casts = [
        'gross_sale' => 'decimal:2',
        'author_percentage' => 'decimal:2',
        'author_earning' => 'decimal:2',
        'platform_earning' => 'decimal:2',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function orderItem()
    {
        return $this->belongsTo(OrderItem::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function payoutRequest()
    {
        return $this->belongsTo(PayoutRequest::class);
    }
}
