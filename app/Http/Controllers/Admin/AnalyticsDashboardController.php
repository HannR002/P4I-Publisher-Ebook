<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\LibraryItem;
use App\Models\ManualOrder;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardController extends Controller
{
    public function index()
    {
        $period = fn (string $event, int $days) => AnalyticsEvent::where('event_type', $event)->where('occurred_at', '>=', now()->subDays($days))->count();
        $engagement = ['reads_today' => $period('read_start', 1), 'reads_7' => $period('read_start', 7), 'reads_30' => $period('read_start', 30), 'downloads_30' => $period('download', 30)];

        $inventory = [
            'total' => LibraryItem::count(), 'published' => LibraryItem::where('status', 'published')->count(),
            'draft' => LibraryItem::where('status', 'draft')->count(),
            'new_7' => LibraryItem::where('created_at', '>=', now()->subDays(7))->count(),
            'new_30' => LibraryItem::where('created_at', '>=', now()->subDays(30))->count(),
            'free' => LibraryItem::whereIn('access_policy', ['public_read_download', 'public_read_only', 'registered_read_download', 'registered_read_only', 'external'])->count(),
            'paid' => LibraryItem::whereIn('access_policy', ['manual_purchase', 'physical_only'])->count(),
            'by_type' => LibraryItem::select('type', DB::raw('count(*) as total'))->groupBy('type')->pluck('total', 'type'),
        ];

        $mostRead = LibraryItem::published()->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])->orderByDesc('reads_count')->limit(10)->get();
        $trending = LibraryItem::published()
            ->withCount(['analyticsEvents as recent_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [now()->subDays(7), now()])])
            ->withCount(['analyticsEvents as previous_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [now()->subDays(14), now()->subDays(7)])])
            ->orderByRaw('(recent_count - previous_count) DESC')->limit(10)->get();
        $rarelyRead = LibraryItem::published()->where('published_at', '<=', now()->subDays(config('library.rarely_read_after_days')))
            ->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])
            ->orderBy('reads_count')->get()->where('reads_count', '<', config('library.rarely_read_threshold'))->take(10);
        $topSearches = AnalyticsEvent::where('event_type', 'search')->whereNotNull('search_term')->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(10)->get();
        $zeroSearches = AnalyticsEvent::where('event_type', 'search')->where('result_count', 0)->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(10)->get();
        $financial = [
            'verified' => ManualOrder::whereIn('status', ['verified', 'processing', 'completed'])->sum('total'),
            'pending' => ManualOrder::whereIn('status', ['awaiting_payment', 'payment_submitted'])->sum('total'),
            'rejected' => ManualOrder::where('status', 'rejected')->sum('total'),
        ];

        return view('admin.analytics.index', compact('inventory', 'engagement', 'mostRead', 'trending', 'rarelyRead', 'topSearches', 'zeroSearches', 'financial'));
    }
}
