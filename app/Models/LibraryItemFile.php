<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LibraryItemFile extends Model
{
    protected $fillable = ['library_item_id', 'label', 'file_type', 'mime_type', 'file_path', 'external_url', 'visibility', 'download_allowed', 'is_primary', 'file_size'];
    protected function casts(): array { return ['download_allowed' => 'boolean', 'is_primary' => 'boolean']; }
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
}
