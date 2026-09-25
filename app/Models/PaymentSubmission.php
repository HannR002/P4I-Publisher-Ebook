<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentSubmission extends Model
{
    protected $fillable = ['manual_order_id', 'payment_method_id', 'amount', 'proof_path', 'status', 'submitted_at', 'verified_at', 'verified_by', 'rejection_reason'];
    protected function casts(): array { return ['amount' => 'decimal:2', 'submitted_at' => 'datetime', 'verified_at' => 'datetime']; }
    public function order() { return $this->belongsTo(ManualOrder::class, 'manual_order_id'); }
    public function paymentMethod() { return $this->belongsTo(PaymentMethod::class); }
    public function verifier() { return $this->belongsTo(User::class, 'verified_by'); }
}
