<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class DrmController extends Controller
{
    /**
     * BUG-01 FIX: Removed hardcoded fallback `?? 1`.
     * BUG-05 FIX: Replaced response()->file() with StreamedResponse via fpassthru()
     *             to prevent loading entire PDF into PHP memory.
     */
    public function generateReaderUrl(Request $request, $bookId)
    {
        // BUG-01 FIXED: No fallback to ID 1. Unauthenticated access = 401 immediately.
        $userId = auth()->id();
        if (!$userId) {
            abort(401, 'Unauthenticated. Please log in to access this content.');
        }

        $license = \App\Models\BookLicense::with('book')
            ->where('user_id', $userId)
            ->where('book_id', $bookId)
            ->where('status', 'active')
            ->first();

        if (!$license) {
            abort(403, 'No active license found for this book.');
        }

        $streamUrl = \Illuminate\Support\Facades\URL::temporarySignedRoute(
            'drm.stream',
            now()->addMinutes(15),
            [
                'license_key' => $license->license_key,
                'ip' => $request->ip() // BUG-06 FIX: Bind URL to specific IP
            ]
        );

        return view('reader', [
            'streamUrl'    => $streamUrl,
            'licenseKey'   => $license->license_key,
            'userEmail'    => auth()->user()->email,
            'userIp'       => $request->ip(),
            'lastReadPage' => $license->last_read_page,
        ]);
    }

    public function saveProgress(Request $request)
    {
        $request->validate([
            'license_key' => 'required|string',
            'page' => 'required|integer|min:1',
        ]);

        $license = \App\Models\BookLicense::where('license_key', $request->license_key)
            ->where('user_id', auth()->id())
            ->first();

        if ($license) {
            $license->update(['last_read_page' => $request->page]);
            return response()->json(['success' => true]);
        }

        return response()->json(['success' => false], 403);
    }

    /**
     * BUG-05 FIX: Stream PDF using fpassthru() so only a small buffer is held
     * in memory at any time — not the entire file contents.
     * Previously response()->file() loaded the whole file into PHP RAM.
     */
    public function streamPdf(Request $request, $license_key)
    {
        if (!$request->hasValidSignature()) {
            abort(401, 'Invalid or expired signature.');
        }

        // BUG-06 FIXED: IP Address Binding Validation
        if ($request->ip() !== $request->query('ip')) {
            abort(403, 'IP Address mismatch. URL hijacking detected.');
        }

        $license = \App\Models\BookLicense::with('book')
            ->where('license_key', $license_key)
            ->where('status', 'active')
            ->firstOrFail();

        $book = $license->book;

        if (!$book || !$book->file_path) {
            abort(404, 'Book file not found.');
        }

        $path = \Illuminate\Support\Facades\Storage::disk('local')->path($book->file_path);

        if (!file_exists($path)) {
            abort(404, 'File not found on disk.');
        }

        $fileSize = filesize($path);

        // BUG-05 FIXED: StreamedResponse — PHP only holds one buffer chunk in memory.
        return response()->stream(
            function () use ($path) {
                $stream = fopen($path, 'rb');
                if ($stream === false) {
                    abort(500, 'Unable to open file stream.');
                }
                fpassthru($stream);
                fclose($stream);
            },
            200,
            [
                'Content-Type'        => 'application/pdf',
                'Content-Length'      => $fileSize,
                'Content-Disposition' => 'inline; filename="' . basename($path) . '"',
                'Cache-Control'       => 'no-cache, no-store, must-revalidate',
                'Pragma'              => 'no-cache',
                'Expires'             => '0',
                'X-Accel-Buffering'   => 'no', // Disable nginx buffering
            ]
        );
    }
}
