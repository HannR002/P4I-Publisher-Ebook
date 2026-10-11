<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Symfony\Component\HttpFoundation\Response;

class EnsureUserIsAdmin
{
    /**
     * Handle an incoming request.
     * Abort with 403 if the authenticated user is not an admin.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (! Auth::check() || ! Auth::user()->is_admin) {
            Log::warning('[SECURITY] Unauthorized admin access attempt', ['ip' => request()->ip(), 'url' => request()->path(), 'user_id' => Auth::id() ?? 'guest']);
            abort(403, 'Area ini hanya dapat diakses oleh Administrator.');
        }

        return $next($request);
    }
}
