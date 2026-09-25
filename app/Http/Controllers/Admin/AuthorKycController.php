<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Author;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Storage;

class AuthorKycController extends Controller
{
    /**
     * Display a listing of authors with KYC status filter.
     */
    public function index(Request $request)
    {
        $status = $request->query('status', 'all');
        
        $query = Author::with('user');
        
        if ($status !== 'all') {
            $query->where('kyc_status', $status);
        }
        
        // Order pending first, then latest
        $authors = $query->orderByRaw("CASE WHEN kyc_status = 'pending' THEN 1 ELSE 2 END")
                         ->latest()
                         ->paginate(15);
                         
        return view('admin.kyc.index', compact('authors', 'status'));
    }

    /**
     * Display author KYC details.
     */
    public function show(Author $author)
    {
        $author->load('user');
        return view('admin.kyc.show', compact('author'));
    }

    /**
     * Stream private ID card file securely.
     */
    public function streamIdCard(Author $author)
    {
        if (!$author->id_card_path || !Storage::disk('local')->exists($author->id_card_path)) {
            abort(404, 'Dokumen KTP tidak ditemukan.');
        }

        return response()->file(storage_path('app/' . $author->id_card_path));
    }

    /**
     * Approve author KYC.
     */
    public function approve(Author $author)
    {
        $author->update([
            'kyc_status' => 'verified',
            'verified_at' => now(),
            'rejection_reason' => null
        ]);

        Log::info('[KYC_APPROVED] Author verified', ['author_id' => $author->id]);

        return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi KYC Penulis disetujui.');
    }

    /**
     * Reject author KYC with reason.
     */
    public function reject(Request $request, Author $author)
    {
        $request->validate([
            'rejection_reason' => ['required', 'string', 'min:10']
        ]);

        $author->update([
            'kyc_status' => 'rejected',
            'rejection_reason' => $request->rejection_reason
        ]);

        Log::info('[KYC_REJECTED] Author rejected', ['author_id' => $author->id]);

        return redirect()->route('admin.kyc.index')->with('success', 'Verifikasi KYC Penulis ditolak.');
    }
}
