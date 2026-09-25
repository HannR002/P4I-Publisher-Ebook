<?php

namespace App\Services;

use App\Models\AnalyticsEvent;
use App\Models\LibraryItem;
use Illuminate\Http\Request;

class AnalyticsRecorder
{
    private const ALLOWED_EVENTS = ['detail_view', 'read_start', 'read_progress', 'download', 'search', 'search_result_click', 'external_open', 'payment_started', 'payment_submitted'];

    public function record(string $event, Request $request, ?LibraryItem $item = null, array $data = []): void
    {
        if (! in_array($event, self::ALLOWED_EVENTS, true)) return;

        AnalyticsEvent::create([
            'event_type' => $event,
            'library_item_id' => $item?->id,
            'user_id' => $request->user()?->id,
            'anonymous_id' => $request->user() ? null : hash('sha256', $request->session()->getId()),
            'search_term' => isset($data['search_term']) ? mb_substr((string) $data['search_term'], 0, 255) : null,
            'result_count' => $data['result_count'] ?? null,
            'metadata' => array_intersect_key($data, array_flip(['type', 'category', 'access', 'format', 'year', 'sort'])),
            'occurred_at' => now(),
        ]);
    }
}
