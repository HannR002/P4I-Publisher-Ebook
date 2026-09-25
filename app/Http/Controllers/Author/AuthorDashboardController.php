<?php

namespace App\Http\Controllers\Author;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class AuthorDashboardController extends Controller
{
    public function index(Request $request)
    {
        $author = $request->user()->authorProfile;
        $availableBalance = config('features.royalty') ? $author->available_balance : 0;
        $publishedBooksCount = $author->submissions()->where('status', 'published')->count();
        $inReviewCount = $author->submissions()->whereIn('status', ['submitted', 'in_review'])->count();

        return view('author.dashboard', compact('author', 'availableBalance', 'publishedBooksCount', 'inReviewCount'));
    }
}
