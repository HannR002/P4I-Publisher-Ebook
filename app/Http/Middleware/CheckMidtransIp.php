<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;
use Symfony\Component\HttpFoundation\IpUtils;
use Illuminate\Support\Facades\Log;

class CheckMidtransIp
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Bypass IP check if not in production or midtrans.is_production is false
        if (!app()->isProduction() || config('midtrans.is_production') == false) {
            return $next($request);
        }

        $midtransIps = [
            '103.28.248.0/22',
            '103.28.244.0/22',
        ];

        if (!IpUtils::checkIp($request->ip(), $midtransIps)) {
            Log::warning('[Security] Unauthorized Midtrans Webhook attempt from IP: ' . $request->ip());
            return response()->json(['message' => 'Forbidden'], 403);
        }

        return $next($request);
    }
}
