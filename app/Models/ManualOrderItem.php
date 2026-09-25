<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class ManualOrderItem extends Model
{
    protected $fillable = ['manual_order_id', 'item_type', 'item_id', 'description', 'quantity', 'unit_price', 'subtotal'];
    protected function casts(): array { return ['unit_price' => 'decimal:2', 'subtotal' => 'decimal:2']; }
    public function order() { return $this->belongsTo(ManualOrder::class, 'manual_order_id'); }
}
