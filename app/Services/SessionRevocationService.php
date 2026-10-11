<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class SessionRevocationService
{
    public function revoke(int $userId, ?string $keepSessionId = null): void
    {
        if (config('session.driver') !== 'database') {
            return;
        }
        $query = DB::connection(config('session.connection'))->table(config('session.table', 'sessions'))->where('user_id', $userId);
        if ($keepSessionId !== null) {
            $query->where('id', '!=', $keepSessionId);
        }
        $query->delete();
    }
}
