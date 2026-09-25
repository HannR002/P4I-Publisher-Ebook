<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LibraryItem extends Model
{
    protected $fillable = [
        'type', 'title', 'slug', 'description', 'synopsis', 'abstract',
        'featured_excerpt', 'excerpt_source', 'excerpt_page', 'publisher',
        'publication_date', 'publication_year', 'isbn', 'issn', 'doi',
        'language', 'cover_path', 'keywords', 'source_type', 'source_url',
        'access_policy', 'price', 'status', 'metadata', 'published_at',
    ];

    protected function casts(): array
    {
        return [
            'metadata' => 'array',
            'price' => 'decimal:2',
            'publication_date' => 'date',
            'published_at' => 'datetime',
        ];
    }

    protected static function booted(): void
    {
        static::creating(function (LibraryItem $item): void {
            if (! $item->slug) {
                $base = Str::slug($item->title) ?: Str::lower(Str::random(8));
                $slug = $base;
                $counter = 2;
                while (static::where('slug', $slug)->exists()) {
                    $slug = "{$base}-{$counter}";
                    $counter++;
                }
                $item->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creators() { return $this->hasMany(LibraryItemCreator::class)->orderBy('sort_order'); }
    public function files() { return $this->hasMany(LibraryItemFile::class); }
    public function primaryFile() { return $this->hasOne(LibraryItemFile::class)->where('is_primary', true); }
    public function categories() { return $this->belongsToMany(Category::class)->withTimestamps(); }
    public function editions() { return $this->hasMany(BookEdition::class); }
    public function legacyBook() { return $this->hasOne(Book::class); }
    public function analyticsEvents() { return $this->hasMany(AnalyticsEvent::class); }
    public function accessGrants() { return $this->hasMany(LibraryAccessGrant::class); }

    public function scopePublished(Builder $query): Builder
    {
        return $query->where('status', 'published')
            ->where(fn (Builder $q) => $q->whereNull('published_at')->orWhere('published_at', '<=', now()));
    }

    public function scopeSearch(Builder $query, ?string $term): Builder
    {
        $term = trim((string) $term);
        if ($term === '') return $query;

        $like = "%{$term}%";
        return $query->where(function (Builder $q) use ($like): void {
            $q->where('title', 'like', $like)
                ->orWhere('description', 'like', $like)
                ->orWhere('synopsis', 'like', $like)
                ->orWhere('abstract', 'like', $like)
                ->orWhere('isbn', 'like', $like)
                ->orWhere('issn', 'like', $like)
                ->orWhere('doi', 'like', $like)
                ->orWhere('publisher', 'like', $like)
                ->orWhere('keywords', 'like', $like)
                ->orWhereRaw('CAST(publication_year AS CHAR) LIKE ?', [$like])
                ->orWhereHas('creators', fn (Builder $creator) => $creator->where('name', 'like', $like))
                ->orWhereHas('categories', fn (Builder $category) => $category->where('name', 'like', $like));
        });
    }
}
