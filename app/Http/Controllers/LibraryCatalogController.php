<?php

namespace App\Http\Controllers;

use App\Models\AnalyticsEvent;
use App\Models\Category;
use App\Models\LibraryItem;
use App\Services\AnalyticsRecorder;
use App\Services\LibraryAccessService;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class LibraryCatalogController extends Controller
{
    public function index(Request $request, AnalyticsRecorder $analytics)
    {
        $query = LibraryItem::query()->published()->with(['creators', 'categories', 'primaryFile']);

        $query->search($request->string('q')->toString());
        $query->when($request->filled('type'), fn (Builder $q) => $q->where('type', $request->type));
        $query->when($request->filled('category'), fn (Builder $q) => $q->whereHas('categories', fn (Builder $c) => $c->where('categories.slug', $request->category)));
        $query->when($request->filled('creator'), fn (Builder $q) => $q->whereHas('creators', fn (Builder $c) => $c->where('name', 'like', '%'.$request->creator.'%')));
        $query->when($request->filled('publisher'), fn (Builder $q) => $q->where('publisher', $request->publisher));
        $query->when($request->filled('year'), fn (Builder $q) => $q->where('publication_year', $request->integer('year')));
        $query->when($request->filled('language'), fn (Builder $q) => $q->where('language', $request->language));

        match ($request->access) {
            'free' => $query->whereIn('access_policy', ['public_read_download', 'public_read_only', 'registered_read_download', 'registered_read_only', 'external']),
            'paid' => $query->whereIn('access_policy', ['manual_purchase', 'physical_only']),
            'public_read' => $query->whereIn('access_policy', ['public_read_download', 'public_read_only']),
            'download_available' => $query->whereHas('files', fn (Builder $q) => $q->where('download_allowed', true)),
            default => null,
        };

        match ($request->format) {
            'digital' => $query->whereHas('files'),
            'print' => $query->whereHas('editions', fn (Builder $q) => $q->whereIn('format', ['softcover', 'hardcover'])->where('is_active', true)),
            default => null,
        };

        match ($request->sort) {
            'a-z' => $query->orderBy('title'),
            'most_read' => $query->withCount(['analyticsEvents as reads_count' => fn (Builder $q) => $q->where('event_type', 'read_start')])->orderByDesc('reads_count'),
            'most_downloaded' => $query->withCount(['analyticsEvents as downloads_count' => fn (Builder $q) => $q->where('event_type', 'download')])->orderByDesc('downloads_count'),
            'trending' => $query->withCount(['analyticsEvents as trend_count' => fn (Builder $q) => $q->whereIn('event_type', ['detail_view', 'read_start', 'download'])->where('occurred_at', '>=', now()->subDays(7))])->orderByDesc('trend_count')->orderByDesc('published_at'),
            default => $query->orderByDesc('published_at')->orderByDesc('id'),
        };

        $items = $query->paginate(18)->withQueryString();

        if ($request->filled('q')) {
            $analytics->record('search', $request, null, [
                'search_term' => $request->q,
                'result_count' => $items->total(),
                'type' => $request->type,
                'category' => $request->category,
                'access' => $request->access,
                'format' => $request->format,
                'year' => $request->year,
                'sort' => $request->sort,
            ]);
        }

        return view('library.index', [
            'items' => $items,
            'categories' => Category::orderBy('name')->get(),
            'types' => config('library.types'),
            'years' => LibraryItem::published()->whereNotNull('publication_year')->distinct()->orderByDesc('publication_year')->pluck('publication_year'),
        ]);
    }

    public function show(Request $request, LibraryItem $libraryItem, LibraryAccessService $access, AnalyticsRecorder $analytics)
    {
        abort_unless($libraryItem->status === 'published', 404);
        $libraryItem->load(['creators', 'categories', 'files', 'editions', 'parent', 'children' => fn($q) => $q->published()]);
        $analytics->record('detail_view', $request, $libraryItem);

        return view('library.show', ['item' => $libraryItem, 'actions' => $access->actions($libraryItem, $request->user())]);
    }

    public function read(Request $request, LibraryItem $libraryItem, LibraryAccessService $access, AnalyticsRecorder $analytics)
    {
        if (! $access->canRead($libraryItem, $request->user())) {
            return $request->user() ? abort(403) : redirect()->guest(route('login'));
        }

        if ($libraryItem->access_policy === 'external') {
            $analytics->record('external_open', $request, $libraryItem);
            return redirect()->away($this->safeExternalUrl($libraryItem->source_url));
        }

        $file = $libraryItem->files()->where('is_primary', true)->first() ?: $libraryItem->files()->first();
        abort_unless($file, 404, 'Berkas publikasi belum tersedia.');
        if ($file->external_url) {
            $analytics->record('external_open', $request, $libraryItem);
            return redirect()->away($this->safeExternalUrl($file->external_url));
        }

        abort_unless($file->file_path && Storage::disk('local')->exists($file->file_path), 404);
        $analytics->record('read_start', $request, $libraryItem);

        return response()->file(Storage::disk('local')->path($file->file_path), [
            'Content-Type' => $file->mime_type ?: 'application/pdf',
            'Content-Disposition' => 'inline; filename="'.basename($file->file_path).'"',
            'X-Content-Type-Options' => 'nosniff',
        ]);
    }

    public function download(Request $request, LibraryItem $libraryItem, LibraryAccessService $access, AnalyticsRecorder $analytics)
    {
        abort_unless($access->canDownload($libraryItem, $request->user()), 403);
        $file = $libraryItem->files()->where('download_allowed', true)->orderByDesc('is_primary')->firstOrFail();
        if ($file->external_url) return redirect()->away($this->safeExternalUrl($file->external_url));
        abort_unless($file->file_path && Storage::disk('local')->exists($file->file_path), 404);
        $analytics->record('download', $request, $libraryItem);

        return Storage::disk('local')->download($file->file_path, basename($file->file_path), ['X-Content-Type-Options' => 'nosniff']);
    }

    public function trackClick(Request $request, LibraryItem $libraryItem, AnalyticsRecorder $analytics)
    {
        $analytics->record('search_result_click', $request, $libraryItem, ['search_term' => $request->input('q')]);
        return redirect()->route('library.show', $libraryItem);
    }

    private function safeExternalUrl(?string $url): string
    {
        abort_unless($url && in_array(parse_url($url, PHP_URL_SCHEME), ['http', 'https'], true), 422, 'URL sumber tidak valid.');
        return $url;
    }
}
