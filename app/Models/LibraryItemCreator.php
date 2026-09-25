<?php
namespace App\Models;
use Illuminate\Database\Eloquent\Model;
class LibraryItemCreator extends Model
{
    protected $fillable = ['library_item_id', 'name', 'role', 'sort_order', 'external_identifier'];
    public function libraryItem() { return $this->belongsTo(LibraryItem::class); }
}
