<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionReview extends Model
{
    protected $fillable = [
        'submission_id',
        'reviewer_id',
        'feedback',
        'action',
    ];

    public function submission()
    {
        return $this->belongsTo(BookSubmission::class, 'submission_id');
    }

    public function reviewer()
    {
        return $this->belongsTo(User::class, 'reviewer_id');
    }
}
