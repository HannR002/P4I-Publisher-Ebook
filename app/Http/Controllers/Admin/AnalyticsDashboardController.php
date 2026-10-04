<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\LibraryItem;
use App\Models\ManualOrder;
use App\Models\BookSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;

class AnalyticsDashboardController extends Controller
{
    public function index()
    {
        // 1. Executive Summary Metrics
        $period = fn (string $event, int $days) => AnalyticsEvent::where('event_type', $event)->where('occurred_at', '>=', now()->subDays($days))->count();
        $engagement = [
            'reads_today' => $period('read_start', 1),
            'downloads_today' => $period('download', 1),
        ];

        $inventory = [
            'total' => LibraryItem::count(),
            'books' => LibraryItem::where('type', 'book')->count(),
            'journals' => LibraryItem::where('type', 'journal')->count(),
            'new_30' => LibraryItem::where('created_at', '>=', now()->subDays(30))->count(),
            'by_type' => LibraryItem::select('type', DB::raw('count(*) as total'))->groupBy('type')->pluck('total', 'type'),
        ];

        // 2. Action Items (Perlu Tindakan)
        $actions = [
            'pending_payments' => ManualOrder::where('status', 'payment_submitted')->count(),
            'pending_reviews' => BookSubmission::whereIn('status', ['submitted', 'in_review'])->count(),
            'needs_revision' => BookSubmission::where('status', 'revision_requested')->count(),
            'missing_cover' => LibraryItem::whereNull('cover_path')->count(),
            // A simple proxy for missing files (requires joining or checking source_url for internal files, but simple check first)
            'missing_file' => LibraryItem::whereIn('source_type', ['pdf', 'epub'])->whereNull('source_url')->count(),
        ];

        // 3. Publishing Pipeline Preview
        $publishing = [
            'draft' => BookSubmission::where('status', 'draft')->count(),
            'submitted' => BookSubmission::where('status', 'submitted')->count(),
            'in_review' => BookSubmission::where('status', 'in_review')->count(),
            'revision_requested' => BookSubmission::where('status', 'revision_requested')->count(),
            'approved' => BookSubmission::where('status', 'approved')->count(),
            'rejected' => BookSubmission::where('status', 'rejected')->count(),
            'published' => BookSubmission::where('status', 'published')->count(),
        ];

        // 4. Recent Collection Activity (5-8 records)
        $recentCollections = LibraryItem::with('creators')->latest()->take(6)->get();

        // 5. Engagement Preview
        $mostRead = LibraryItem::published()->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])->orderByDesc('reads_count')->limit(5)->get();
        $trending = LibraryItem::published()
            ->withCount(['analyticsEvents as recent_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [now()->subDays(7), now()])])
            ->withCount(['analyticsEvents as previous_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [now()->subDays(14), now()->subDays(7)])])
            ->orderByRaw('(recent_count - previous_count) DESC')->limit(5)->get();

        // 6. Search Demand Preview
        $topSearches = AnalyticsEvent::where('event_type', 'search')->whereNotNull('search_term')->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(5)->get();
        $zeroSearches = AnalyticsEvent::where('event_type', 'search')->where('result_count', 0)->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(5)->get();

        // 7. Legacy test support
        $rarelyRead = LibraryItem::published()->where('published_at', '<=', now()->subDays(config('library.rarely_read_after_days', 30)))
            ->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])
            ->orderBy('reads_count')->get()->where('reads_count', '<', config('library.rarely_read_threshold', 5))->take(10);

        // 8. Feature Status
        $featureStatus = [
            'Library' => 'Aktif',
            'Manual Payment' => 'Aktif',
            'Midtrans' => config('features.midtrans', false) ? 'Aktif' : 'Nonaktif',
            'Royalty' => config('features.royalty', false) ? 'Aktif' : 'Nonaktif',
            'Payout' => config('features.payout', false) ? 'Aktif' : 'Nonaktif',
            'Financial KYC' => config('features.author_kyc', false) ? 'Aktif' : 'Nonaktif',
        ];

        return view('admin.analytics.index', compact(
            'inventory', 'engagement', 'actions', 'publishing', 'recentCollections', 'mostRead', 'trending', 'topSearches', 'zeroSearches', 'rarelyRead', 'featureStatus'
        ));
    }
}
