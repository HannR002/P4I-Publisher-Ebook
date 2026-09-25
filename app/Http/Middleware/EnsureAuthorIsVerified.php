<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureAuthorIsVerified
{
    /**
     * Handle an incoming request.
     *
     * @param  Closure(Request): (Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! config('features.author_kyc')) {
            return $next($request);
        }

        $user = \Illuminate\Support\Facades\Auth::user();
        
        if (!$user || !$user->authorProfile || !$user->authorProfile->isVerified()) {
            return redirect()->route('author.kyc-status')->with('warning', 'Akses ditangguhkan. Akun penulis Anda belum terverifikasi atau sedang dalam proses peninjauan.');
        }

        return $next($request);
    }
}
