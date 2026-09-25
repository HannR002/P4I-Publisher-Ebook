<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Book extends Model
{
    protected $fillable = [
        'title',
        'library_item_id',
        'slug',
        'author',
        'author_id',
        'description',
        'price',
        'isbn',
        'pages',
        'publish_date',
        'cover_image_path',
        'file_path',
        'is_published',
    ];

    public function licenses()
    {
        return $this->hasMany(BookLicense::class);
    }

    public function authorRelation()
    {
        return $this->belongsTo(Author::class, 'author_id');
    }

    public function royaltyLedgers()
    {
        return $this->hasMany(RoyaltyLedger::class);
    }

    public function reviews()
    {
        return $this->hasMany(Review::class);
    }

    public function categories()
    {
        return $this->belongsToMany(Category::class);
    }

    public function libraryItem()
    {
        return $this->belongsTo(LibraryItem::class);
    }

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($book) {
            if (empty($book->slug)) {
                $slug = \Illuminate\Support\Str::slug($book->title);
                $originalSlug = $slug;
                $count = 1;

                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$originalSlug}-{$count}";
                    $count++;
                }

                $book->slug = $slug;
            }
        });

        static::saved(function (Book $book) {
            app(\App\Services\LibraryItemSynchronizer::class)->sync($book);
        });
    }

    protected function casts(): array
    {
        return ['publish_date' => 'date', 'is_published' => 'boolean', 'price' => 'decimal:2'];
    }
}
