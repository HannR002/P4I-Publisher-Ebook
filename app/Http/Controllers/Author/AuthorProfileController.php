<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use App\Http\Requests\Author\RegisterAuthorRequest;
use App\Models\Author;
use Illuminate\Support\Facades\Auth;

class AuthorProfileController extends Controller
{
    public function create()
    {
        return view('author.register');
    }

    public function store(RegisterAuthorRequest $request)
    {
        $idCardPath = config('features.author_kyc')
            ? $request->file('id_card_file')->store('private/kyc', 'local')
            : null;

        Author::create([
            'user_id' => Auth::id(),
            'pen_name' => $request->pen_name,
            'bio' => $request->bio,
            // Legacy column is now nullable; a true null value preserves the
            // new schema without requiring or collecting a KTP number.
            'id_card_number' => config('features.author_kyc') ? $request->id_card_number : null,
            'id_card_path' => $idCardPath,
            'bank_name' => $request->bank_name,
            'bank_account' => $request->bank_account,
            'bank_holder_name' => $request->bank_holder_name,
            'kyc_status' => config('features.author_kyc') ? 'pending' : 'unverified',
        ]);

        if (! config('features.author_kyc')) {
            return redirect()->route('author.dashboard')->with('success', 'Profil penulis berhasil dibuat. Anda dapat langsung mengajukan naskah.');
        }

        return redirect()->route('author.kyc-status')->with('success', 'Pendaftaran berhasil dikirim dan sedang ditinjau.');
    }

    public function kycStatus()
    {
        $author = Auth::user()->authorProfile;
        if (!$author) {
            return redirect()->route('author.register');
        }

        return view('author.kyc-status', compact('author'));
    }

    public function dashboard()
    {
        $author = Auth::user()->authorProfile;

        $availableBalance = $author->available_balance;
        $publishedBooksCount = $author->submissions()->where('status', 'published')->count();
        $inReviewCount = $author->submissions()->whereIn('status', ['submitted', 'in_review'])->count();

        return view('author.dashboard', compact('author', 'availableBalance', 'publishedBooksCount', 'inReviewCount'));
    }
}
