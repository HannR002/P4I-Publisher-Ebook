<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\StoreSubmissionRequest;
use App\Http\Requests\Author\UpdateSubmissionRequest;
use App\Models\BookSubmission;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class BookSubmissionController extends Controller
{
    public function index()
    {
        $submissions = Auth::user()->authorProfile->submissions()->latest()->paginate(10);
        return view('author.submissions.index', compact('submissions'));
    }

    public function create()
    {
        return view('author.submissions.create');
    }

    public function store(StoreSubmissionRequest $request)
    {
        $path = $request->file('manuscript_file')->store('private/submissions', 'local');
        
        $coverPath = null;
        if ($request->hasFile('cover_preview')) {
            $coverPath = $request->file('cover_preview')->store('covers', 'public');
        }

        $status = $request->input('action') === 'draft' ? 'draft' : 'submitted';

        BookSubmission::create([
            'author_id' => Auth::user()->authorProfile->id,
            'category_id' => $request->category_id,
            'title' => $request->title,
            'synopsis' => $request->synopsis,
            'proposed_price' => $request->proposed_price,
            'manuscript_path' => $path,
            'cover_preview_path' => $coverPath,
            'status' => $status,
        ]);

        return redirect()->route('author.submissions.index')->with('success', 'Naskah berhasil disimpan.');
    }

    public function show(BookSubmission $submission)
    {
        if ($submission->author_id !== Auth::user()->authorProfile->id) {
            abort(403);
        }

        $submission->load('reviews.reviewer');
        return view('author.submissions.show', compact('submission'));
    }

    public function edit(BookSubmission $submission)
    {
        if ($submission->author_id !== Auth::user()->authorProfile->id) {
            abort(403);
        }

        if (!in_array($submission->status, ['draft', 'revision_requested'])) {
            return redirect()->route('author.submissions.index')->with('error', 'Naskah ini sedang dalam proses peninjauan dan tidak dapat diubah.');
        }

        return view('author.submissions.edit', compact('submission'));
    }

    public function update(UpdateSubmissionRequest $request, BookSubmission $submission)
    {
        $data = $request->only(['category_id', 'title', 'synopsis', 'proposed_price']);

        if ($request->hasFile('manuscript_file')) {
            if ($submission->status === 'revision_requested') {
                \App\Models\SubmissionRevision::create([
                    'submission_id' => $submission->id,
                    'manuscript_path' => $submission->manuscript_path,
                    'revision_note' => $request->input('revision_note'),
                ]);
            } else {
                Storage::disk('local')->delete($submission->manuscript_path);
            }
            $data['manuscript_path'] = $request->file('manuscript_file')->store('private/submissions', 'local');
        }

        if ($request->hasFile('cover_preview')) {
            if ($submission->cover_preview_path) {
                Storage::disk('public')->delete($submission->cover_preview_path);
            }
            $data['cover_preview_path'] = $request->file('cover_preview')->store('covers', 'public');
        }

        if ($submission->status === 'revision_requested' || $request->input('action') === 'submit') {
            $data['status'] = 'submitted';
        }

        $submission->update($data);

        return redirect()->route('author.submissions.index')->with('success', 'Naskah berhasil diperbarui.');
    }
}
