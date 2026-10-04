<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\AnalyticsEvent;
use App\Models\LibraryItem;
use App\Models\ManualOrder;
use App\Models\PaymentSubmission;
use App\Models\BookSubmission;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Facades\DB;
use Illuminate\Http\Request;

class FullAnalyticsController extends Controller
{
    public function index(Request $request)
    {
        $period = (int) $request->input('period', 30);
        if (!in_array($period, [7, 30, 90])) {
            $period = 30;
        }

        $startDate = now()->subDays($period);

        // 1. Engagement & Popularity (filtered by period)
        $mostRead = LibraryItem::published()
            ->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')->where('occurred_at', '>=', $startDate)])
            ->orderByDesc('reads_count')->limit(20)->get();

        $mostDownloaded = LibraryItem::published()
            ->withCount(['analyticsEvents as downloads_count' => fn (Builder $q) => $q->where('event_type', 'download')->where('occurred_at', '>=', $startDate)])
            ->orderByDesc('downloads_count')->limit(20)->get();

        // Trending: Compare selected period with the immediately preceding equal period
        $trending = LibraryItem::published()
            ->withCount(['analyticsEvents as recent_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [$startDate, now()])])
            ->withCount(['analyticsEvents as previous_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->whereBetween('occurred_at', [now()->subDays($period * 2), $startDate])])
            ->orderByRaw('(recent_count - previous_count) DESC')->limit(20)->get();

        // 2. Search Demand Insights
        $topSearches = AnalyticsEvent::where('event_type', 'search')->whereNotNull('search_term')
            ->where('occurred_at', '>=', $startDate)
            ->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(20)->get();
            
        $zeroSearches = AnalyticsEvent::where('event_type', 'search')->where('result_count', 0)
            ->where('occurred_at', '>=', $startDate)
            ->select('search_term', DB::raw('count(*) as total'))->groupBy('search_term')->orderByDesc('total')->limit(20)->get();

        // 3. Content Health
        $rarelyRead = LibraryItem::published()->where('published_at', '<=', now()->subDays(config('library.rarely_read_after_days', 30)))
            ->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])
            ->having('reads_count', '<', config('library.rarely_read_threshold', 5))
            ->orderBy('reads_count')->limit(20)->get();
            
        $contentIssues = [
            'missing_cover' => LibraryItem::whereNull('cover_path')->count(),
            'missing_file' => LibraryItem::whereIn('source_type', ['pdf', 'epub'])->whereNull('source_url')->count(),
            'missing_description' => LibraryItem::whereNull('description')->orWhere('description', '')->count(),
            'missing_isbn' => LibraryItem::where('type', 'book')->where(function($q) {
                $q->whereNull('isbn')->orWhere('isbn', '');
            })->count(),
            'missing_issn' => LibraryItem::where('type', 'journal')->where(function($q) {
                $q->whereNull('issn')->orWhere('issn', '');
            })->count(),
            'drafts' => LibraryItem::where('is_published', false)->count(),
        ];

        // 4. Over-time Chart Data (Dynamic Period)
        $engagementOverTime = AnalyticsEvent::whereIn('event_type', ['read_start', 'download'])
            ->where('occurred_at', '>=', $startDate)
            ->select(DB::raw('DATE(occurred_at) as date'), 'event_type', DB::raw('count(*) as total'))
            ->groupBy('date', 'event_type')
            ->orderBy('date', 'asc')
            ->get();

        // 5. Payment Analytics
        $payments = [
            'awaiting_payment' => ManualOrder::where('status', 'awaiting_payment')->where('created_at', '>=', $startDate)->count(),
            'payment_submitted' => ManualOrder::where('status', 'payment_submitted')->where('created_at', '>=', $startDate)->count(),
            'verified' => ManualOrder::where('status', 'success')->where('created_at', '>=', $startDate)->count(),
            'rejected' => ManualOrder::where('status', 'failed')->where('created_at', '>=', $startDate)->count(),
            'realized_revenue' => ManualOrder::where('status', 'success')->where('created_at', '>=', $startDate)->sum('total_amount'),
        ];
        
        // Payment Breakdown by Method
        $paymentBreakdown = ManualOrder::where('status', 'success')->where('created_at', '>=', $startDate)
            ->join('payment_methods', 'manual_orders.payment_method_id', '=', 'payment_methods.id')
            ->select('payment_methods.name', DB::raw('count(*) as total_orders'), DB::raw('sum(total_amount) as total_revenue'))
            ->groupBy('payment_methods.name')
            ->get();

        // 6. Publishing Analytics
        $publishing = [
            'submitted' => BookSubmission::where('status', 'submitted')->count(),
            'in_review' => BookSubmission::where('status', 'in_review')->count(),
            'revision_requested' => BookSubmission::where('status', 'revision_requested')->count(),
            'approved' => BookSubmission::where('status', 'approved')->count(),
            'published' => BookSubmission::where('status', 'published')->count(),
            'rejected' => BookSubmission::where('status', 'rejected')->count(),
        ];
        
        $oldestPendingSubmission = BookSubmission::whereIn('status', ['submitted', 'in_review'])->oldest('created_at')->first();

        return view('admin.analytics.full', compact(
            'period', 'mostRead', 'mostDownloaded', 'trending', 'topSearches', 'zeroSearches', 
            'rarelyRead', 'contentIssues', 'engagementOverTime', 'payments', 'paymentBreakdown', 
            'publishing', 'oldestPendingSubmission'
        ));
    }
}
