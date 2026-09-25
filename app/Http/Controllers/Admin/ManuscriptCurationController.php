<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Book;
use App\Models\BookSubmission;
use App\Models\SubmissionReview;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Str;

class ManuscriptCurationController extends Controller
{
    /**
     * Display a listing of manuscript submissions.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        
        $query = BookSubmission::with(['author.user', 'category']);
        
        if ($status !== 'all') {
            $query->where('status', $status);
        }
        
        // Order by latest
        $submissions = $query->orderByRaw("CASE WHEN status = 'submitted' THEN 1 WHEN status = 'revision_requested' THEN 2 ELSE 3 END")
                             ->latest()
                             ->paginate(15);
                             
        return view('admin.submissions.index', compact('submissions', 'status'));
    }

    /**
     * Display manuscript details.
     */
    public function show(BookSubmission $submission)
    {
        // Auto update status to in_review if it was just submitted and viewed by admin
        if ($submission->status === 'submitted') {
            $submission->update(['status' => 'in_review']);
        }
        
        $submission->load(['author.user', 'category', 'reviews.reviewer']);
        
        return view('admin.submissions.show', compact('submission'));
    }

    /**
     * Stream private manuscript file securely.
     */
    public function streamManuscript(BookSubmission $submission)
    {
        if (!$submission->manuscript_path || !Storage::disk('local')->exists($submission->manuscript_path)) {
            abort(404, 'Dokumen Naskah tidak ditemukan.');
        }

        return response()->file(storage_path('app/' . $submission->manuscript_path));
    }

    /**
     * Request revision for manuscript.
     */
    public function requestRevision(Request $request, BookSubmission $submission)
    {
        $request->validate([
            'feedback' => ['required', 'string', 'min:10']
        ]);

        DB::transaction(function () use ($request, $submission) {
            SubmissionReview::create([
                'submission_id' => $submission->id,
                'reviewer_id' => auth()->id(),
                'feedback' => $request->feedback,
                'notes' => $request->feedback,
                'action' => 'request_revision'
            ]);

            $submission->update(['status' => 'revision_requested']);
        });

        return redirect()->back()->with('success', 'Permintaan revisi naskah telah dikirim ke penulis.');
    }

    /**
     * Reject manuscript.
     */
    public function reject(Request $request, BookSubmission $submission)
    {
        $request->validate([
            'feedback' => ['required', 'string', 'min:10']
        ]);

        DB::transaction(function () use ($request, $submission) {
            SubmissionReview::create([
                'submission_id' => $submission->id,
                'reviewer_id' => auth()->id(),
                'feedback' => $request->feedback,
                'notes' => $request->feedback,
                'action' => 'reject'
            ]);

            $submission->update(['status' => 'rejected']);
        });

        return redirect()->route('admin.submissions.index')->with('success', 'Naskah berhasil ditolak.');
    }

    /**
     * Approve and Publish manuscript to book.
     */
    public function approveAndPublish(Request $request, BookSubmission $submission)
    {
        $request->validate([
            'final_price' => ['required', 'numeric', 'min:0']
        ]);

        DB::transaction(function () use ($request, $submission) {
            // 1. Move PDF file to private_books
            $newFileName = Str::random(40) . '.pdf';
            Storage::disk('local')->copy($submission->manuscript_path, 'private/private_books/' . $newFileName);

            // 2. Create Book record
            $book = Book::create([
                'title' => $submission->title,
                'author' => $submission->author->pen_name,
                'author_id' => $submission->author->id,
                'price' => $request->final_price,
                'description' => $submission->synopsis,
                'file_path' => 'private/private_books/' . $newFileName,
                'cover_image_path' => $submission->cover_preview_path,
                'is_published' => true,
            ]);

            // 3. Sync Categories if relation exists (if Category book exists)
            // Assuming categories is Many-to-Many or One-to-Many on book.
            // If Category is one to many on book (e.g., category_id), let's check schema.
            // But based on Book model it might have category_id?
            // Will add if Book has category_id. The requirement says: "Tautkan kategori buku jika relasi kategori terdefinisi."
            // Assuming $book->categories()->sync([$submission->category_id]);
            if (method_exists($book, 'categories')) {
                $book->categories()->sync([$submission->category_id]);
            } elseif (\Schema::hasColumn('books', 'category_id')) {
                $book->update(['category_id' => $submission->category_id]);
            }
            app(\App\Services\LibraryItemSynchronizer::class)->sync($book);

            // 4. Create review log
            SubmissionReview::create([
                'submission_id' => $submission->id,
                'reviewer_id' => auth()->id(),
                'feedback' => 'Naskah disetujui dan diterbitkan.',
                'notes' => 'Naskah disetujui dan diterbitkan.',
                'action' => 'approve'
            ]);

            // 5. Update submission
            $submission->update([
                'status' => 'published',
                'book_id' => $book->id
            ]);
        });

        return redirect()->route('admin.books.index')->with('success', 'Naskah berhasil diterbitkan menjadi E-Book resmi!');
    }
}
