<?php

namespace App\Services;

use App\Models\Book;
use App\Models\LibraryItem;
use Illuminate\Support\Facades\Schema;

class LibraryItemSynchronizer
{
    public function sync(Book $book): ?LibraryItem
    {
        if (! Schema::hasTable('library_items') || ! Schema::hasColumn('books', 'library_item_id')) return null;

        $item = $book->library_item_id ? LibraryItem::find($book->library_item_id) : null;
        $item ??= new LibraryItem();
        $item->fill([
            'type' => 'book', 'title' => $book->title, 'slug' => $book->slug,
            'description' => $book->description, 'synopsis' => $book->description,
            'publication_date' => $book->publish_date,
            'publication_year' => $book->publish_date ? (int) $book->publish_date->format('Y') : null,
            'isbn' => $book->isbn, 'cover_path' => $book->cover_image_path,
            'source_type' => 'legacy_book',
            'access_policy' => (float) $book->price > 0 ? 'manual_purchase' : 'public_read_download',
            'price' => $book->price, 'status' => $book->is_published ? 'published' : 'draft',
            'published_at' => $book->is_published ? ($item->published_at ?: now()) : null,
        ]);
        $item->save();

        if (! $book->library_item_id) {
            $book->forceFill(['library_item_id' => $item->id])->saveQuietly();
            $book->setRelation('libraryItem', $item);
        }

        $item->creators()->updateOrCreate(['sort_order' => 0], ['name' => $book->author, 'role' => 'author']);
        $item->files()->updateOrCreate(['is_primary' => true], [
            'label' => 'Berkas utama', 'file_type' => 'pdf', 'mime_type' => 'application/pdf',
            'file_path' => $book->file_path, 'visibility' => 'private',
            'download_allowed' => (float) $book->price === 0.0,
        ]);
        $item->categories()->sync($book->categories()->pluck('categories.id'));

        return $item;
    }

    public function remove(Book $book): void
    {
        if ($book->library_item_id) {
            $item = LibraryItem::find($book->library_item_id);
            if ($item && $item->source_type === 'legacy_book') {
                $item->update(['status' => 'archived']);
            }
        }
    }
}
