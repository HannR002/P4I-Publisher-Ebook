<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureFeatureEnabled
{
    public function handle(Request $request, Closure $next, string $feature): Response
    {
        abort_unless(config("features.{$feature}", false), 404, 'Fitur ini tidak aktif pada versi produk saat ini.');

        return $next($request);
    }
}
