<?php

namespace Tests\Feature;

use App\Models\AnalyticsEvent;
use App\Models\LibraryItem;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class LibraryAnalyticsTest extends TestCase
{
    use RefreshDatabase;

    public function test_detail_read_and_download_events_are_counted(): void
    {
        Storage::fake('local');
        Storage::disk('local')->put('library-items/a.pdf', '%PDF');
        $item = $this->item('Event Item');
        $item->files()->create(['file_type' => 'pdf', 'file_path' => 'library-items/a.pdf', 'visibility' => 'private', 'download_allowed' => true, 'is_primary' => true]);

        $this->get(route('library.show', $item))->assertOk();
        $this->get(route('library.read', $item))->assertOk();
        $this->get(route('library.download', $item))->assertOk();

        $this->assertDatabaseHas('analytics_events', ['library_item_id' => $item->id, 'event_type' => 'detail_view']);
        $this->assertDatabaseHas('analytics_events', ['library_item_id' => $item->id, 'event_type' => 'read_start']);
        $this->assertDatabaseHas('analytics_events', ['library_item_id' => $item->id, 'event_type' => 'download']);
        $this->assertDatabaseMissing('analytics_events', ['metadata' => 'ip']);
    }

    public function test_top_content_rarely_read_and_trending_are_deterministic(): void
    {
        $admin = User::factory()->create(['is_admin' => true]);
        $old = $this->item('Old Quiet', now()->subDays(40));
        $new = $this->item('New Quiet', now()->subDays(2));
        $trend = $this->item('Growing', now()->subDays(40));
        $flat = $this->item('Flat', now()->subDays(40));
        foreach (range(1, 5) as $_) $this->event($trend, 'read_start', now()->subDays(2));
        foreach (range(1, 2) as $_) $this->event($trend, 'read_start', now()->subDays(10));
        foreach (range(1, 4) as $_) $this->event($flat, 'read_start', now()->subDays(10));

        $response = $this->actingAs($admin)->get(route('admin.analytics.index'))->assertOk();
        $this->assertTrue($response->viewData('rarelyRead')->contains('id', $old->id));
        $this->assertFalse($response->viewData('rarelyRead')->contains('id', $new->id));
        $this->assertSame($trend->id, $response->viewData('trending')->first()->id);
        $this->assertSame($trend->id, $response->viewData('mostRead')->first()->id);
    }

    public function test_search_and_zero_result_terms_are_recorded_without_raw_ip(): void
    {
        $this->get('/library?q=not-found-term')->assertOk();
        $this->assertDatabaseHas('analytics_events', ['event_type' => 'search', 'search_term' => 'not-found-term', 'result_count' => 0]);
        $event = AnalyticsEvent::firstOrFail();
        $this->assertNull(data_get($event->metadata, 'ip'));
    }

    private function item(string $title, $publishedAt = null): LibraryItem
    {
        return LibraryItem::create(['type' => 'article', 'title' => $title, 'source_type' => 'local', 'access_policy' => 'public_read_download', 'status' => 'published', 'published_at' => $publishedAt ?: now()]);
    }

    private function event(LibraryItem $item, string $type, $when): void
    {
        AnalyticsEvent::create(['event_type' => $type, 'library_item_id' => $item->id, 'occurred_at' => $when]);
    }
}
