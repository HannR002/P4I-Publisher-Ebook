<?php

namespace App\Http\Controllers;

use App\Models\Book;
use App\Models\BookLicense;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BookController extends Controller
{
    /**
     * Display a listing of the published books.
     */
    public function index(Request $request)
    {
        $query = Book::with('categories')->where('is_published', true);

        if ($request->has('search') && $request->search != '') {
            $searchTerm = $request->search;
            $query->where(function($q) use ($searchTerm) {
                $q->where('title', 'like', '%' . $searchTerm . '%')
                  ->orWhere('author', 'like', '%' . $searchTerm . '%')
                  ->orWhereHas('categories', function($q) use ($searchTerm) {
                      $q->where('name', 'like', '%' . $searchTerm . '%');
                  });
            });
        }

        $books = $query->orderBy('created_at', 'desc')->paginate(12)->withQueryString();

        $ownedBookIds = [];
        if (Auth::check()) {
            $ownedBookIds = BookLicense::where('user_id', Auth::id())
                                       ->where('status', 'active')
                                       ->pluck('book_id')
                                       ->toArray();
        }

        return view('books.index', compact('books', 'ownedBookIds'));
    }

    /**
     * Display the specified book.
     */
    public function show($slug)
    {
        $book = Book::with(['reviews.user', 'categories'])
                    ->where('slug', $slug)
                    ->where('is_published', true)
                    ->firstOrFail();

        if ($book->libraryItem?->status === 'published') {
            return redirect()->route('library.show', $book->libraryItem);
        }

        $isOwned = false;
        $userReview = null;
        if (Auth::check()) {
            $isOwned = BookLicense::where('user_id', Auth::id())
                                  ->where('book_id', $book->id)
                                  ->where('status', 'active')
                                  ->exists();
                                  
            $userReview = $book->reviews()->where('user_id', Auth::id())->first();
        }

        return view('books.show', compact('book', 'isOwned', 'userReview'));
    }

    public function storeReview(Request $request, Book $book)
    {
        $request->validate([
            'rating'  => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string|max:1000',
        ]);

        $isOwned = BookLicense::where('user_id', Auth::id())
                              ->where('book_id', $book->id)
                              ->where('status', 'active')
                              ->exists();

        if (!$isOwned) {
            return back()->with('error', 'Anda harus memiliki buku ini untuk memberikan ulasan.');
        }

        \App\Models\Review::updateOrCreate(
            ['user_id' => Auth::id(), 'book_id' => $book->id],
            ['rating' => $request->rating, 'comment' => $request->comment]
        );

        return back()->with('success', 'Ulasan berhasil disimpan!');
    }
}
