<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BookSubmission extends Model
{
    protected $fillable = [
        'author_id',
        'category_id',
        'title',
        'synopsis',
        'proposed_price',
        'manuscript_path',
        'cover_preview_path',
        'status',
        'book_id',
    ];

    protected $casts = [
        'proposed_price' => 'decimal:2',
    ];

    public function author()
    {
        return $this->belongsTo(Author::class);
    }

    public function category()
    {
        return $this->belongsTo(Category::class);
    }

    public function book()
    {
        return $this->belongsTo(Book::class);
    }

    public function reviews()
    {
        return $this->hasMany(SubmissionReview::class, 'submission_id');
    }
}
