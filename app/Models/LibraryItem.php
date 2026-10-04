<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

class LibraryItem extends Model
{
    protected $fillable = [
        'parent_id', 'type', 'title', 'slug', 'description', 'synopsis', 'abstract',
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

        static::saving(function (LibraryItem $item): void {
            if ($item->parent_id !== null) {
                if ((string)$item->parent_id === (string)$item->id) {
                    throw new \DomainException("A library item cannot be its own parent.");
                }
                $parent = static::find($item->parent_id);
                if ($parent && $parent->parent_id !== null && $item->id !== null && (string)$parent->parent_id === (string)$item->id) {
                    throw new \DomainException("Circular parent relationship detected.");
                }
                if ($parent) {
                    if ($item->type === 'journal') {
                        throw new \DomainException("A journal cannot have a parent.");
                    }
                    if ($item->type === 'journal_issue' && $parent->type !== 'journal') {
                        throw new \DomainException("A journal_issue can only belong to a journal.");
                    }
                    if ($item->type === 'journal_article' && !in_array($parent->type, ['journal', 'journal_issue'], true)) {
                        throw new \DomainException("A journal_article can only belong to a journal or journal_issue.");
                    }
                    if ($item->type === 'book_chapter' && $parent->type !== 'book') {
                        throw new \DomainException("A book_chapter can only belong to a book.");
                    }
                }
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function parent() { return $this->belongsTo(LibraryItem::class, 'parent_id'); }
    public function children() { return $this->hasMany(LibraryItem::class, 'parent_id'); }

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

    public static function getLocalizedType(string $type): string
    {
        return match ($type) {
            'book' => 'Buku',
            'journal' => 'Jurnal',
            'journal_issue' => 'Edisi Jurnal',
            'journal_article' => 'Artikel Jurnal',
            'article' => 'Artikel',
            'proceeding' => 'Prosiding',
            'report' => 'Laporan',
            'module' => 'Modul',
            'monograph' => 'Monograf',
            default => 'Lainnya',
        };
    }
    public static function getLocalizedAccessPolicy(string $policy): string
    {
        return match ($policy) {
            'public_read_download' => 'Baca & Unduh Gratis',
            'public_read_only' => 'Baca Gratis',
            'registered_read_download' => 'Masuk untuk Baca & Unduh',
            'registered_read_only' => 'Masuk untuk Membaca',
            'manual_purchase' => 'Pembelian Manual',
            'physical_only' => 'Versi Cetak',
            'external' => 'Akses Eksternal',
            default => str($policy)->replace('_', ' ')->title()->toString(),
        };
    }
}
