<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LibraryAccessGrant extends Model
{
    protected $fillable = ['user_id', 'library_item_id', 'source_type', 'source_id', 'granted_at', 'expires_at'];
    protected function casts(): array { return ['granted_at' => 'datetime', 'expires_at' => 'datetime']; }
    public function user() { return $this->belongsTo(User::class); }
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
}
