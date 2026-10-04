<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthorBookController extends Controller
{
    public function index(Request $request)
    {
        $author = $request->user()->authorProfile;
        
        // Fetch books actually associated with the author. Eager load libraryItem.
        $books = $author->books()->with('libraryItem')->paginate(12);

        return view('author.books.index', compact('books'));
    }
}
