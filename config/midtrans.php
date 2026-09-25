<?php

return [
    /*
    |--------------------------------------------------------------------------
    | Midtrans Configuration
    |--------------------------------------------------------------------------
    | Server Key  : Used for server-side API calls (keep secret, never expose to frontend)
    | Client Key  : Used for Snap.js initialization (safe to expose to frontend)
    | is_production: Set to true only when going live. Use false for Sandbox.
    | is_sanitized: Auto-sanitize transaction parameters.
    | is_3ds      : Enable 3D-Secure for credit card transactions.
    |
    | To migrate to production: flip MIDTRANS_IS_PRODUCTION=true in .env
    | and swap your keys — no code change needed.
    */

    'merchant_id'   => env('MIDTRANS_MERCHANT_ID'),
    'server_key'    => env('MIDTRANS_SERVER_KEY'),
    'client_key'    => env('MIDTRANS_CLIENT_KEY'),
    'is_production' => env('MIDTRANS_IS_PRODUCTION', false),
    'is_sanitized'  => env('MIDTRANS_IS_SANITIZED', true),
    'is_3ds'        => env('MIDTRANS_IS_3DS', true),
];
