<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;
class ManualOrder extends Model
{
    protected $fillable = ['order_number', 'user_id', 'order_type', 'subtotal', 'shipping_cost', 'total', 'status', 'notes'];
    protected function casts(): array { return ['subtotal' => 'decimal:2', 'shipping_cost' => 'decimal:2', 'total' => 'decimal:2']; }
    protected static function booted(): void { static::creating(fn (ManualOrder $order) => $order->order_number ??= 'P4I-'.now()->format('Ymd').'-'.Str::upper(Str::random(8))); }
    public function user() { return $this->belongsTo(User::class); }
    public function items() { return $this->hasMany(ManualOrderItem::class); }
    public function paymentSubmissions() { return $this->hasMany(PaymentSubmission::class); }
}
