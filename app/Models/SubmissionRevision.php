<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SubmissionRevision extends Model
{
    protected $fillable = [
        'submission_id',
        'manuscript_path',
        'revision_note',
    ];

    public function submission()
    {
        return $this->belongsTo(BookSubmission::class, 'submission_id');
    }
}
