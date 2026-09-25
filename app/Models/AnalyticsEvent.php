<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class AnalyticsEvent extends Model
{
    protected $fillable = ['event_type', 'library_item_id', 'user_id', 'anonymous_id', 'search_term', 'result_count', 'metadata', 'occurred_at'];
    protected function casts(): array { return ['metadata' => 'array', 'occurred_at' => 'datetime']; }
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
    public function user() { return $this->belongsTo(User::class); }
}
