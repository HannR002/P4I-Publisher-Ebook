<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class BookEdition extends Model
{
    protected $fillable = ['library_item_id', 'format', 'edition_name', 'isbn', 'sku', 'price', 'stock', 'weight', 'width', 'height', 'thickness', 'is_active'];
    protected function casts(): array { return ['price' => 'decimal:2', 'is_active' => 'boolean']; }
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
}
