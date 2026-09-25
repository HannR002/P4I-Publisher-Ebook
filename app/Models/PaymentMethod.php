<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class PaymentMethod extends Model
{
    protected $fillable = ['type', 'name', 'account_name', 'account_number', 'qr_image_path', 'instructions', 'is_active', 'sort_order'];
    protected function casts(): array { return ['is_active' => 'boolean']; }
    public function paymentSubmissions() { return $this->hasMany(PaymentSubmission::class); }
}
