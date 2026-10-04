<x-admin-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:justify-between sm:items-center gap-4">
            <h2 class="font-bold text-2xl text-text-primary">Perpustakaan · Semua Koleksi</h2>
            <x-button href="{{ route('admin.library.create') }}" variant="primary">Tambah Koleksi</x-button>
        </div>
    </x-slot>

    <!-- Filters/Search (Visual representation of controls) -->
    <div class="bg-surface border border-border rounded-xl p-4 mb-6 flex flex-wrap gap-4">
        <form method="GET" action="{{ route('admin.library.index') }}" class="flex-1 min-w-[200px] flex gap-2">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Cari judul, penulis, ISBN..." class="flex-1 bg-background border border-border text-text-primary rounded-lg px-4 py-2 focus:ring-primary focus:border-primary text-sm">
            <select name="type" class="bg-background border border-border text-text-primary rounded-lg px-4 py-2 focus:ring-primary focus:border-primary text-sm w-32">
                <option value="">Semua Tipe</option>
                @foreach(config('library.types', ['book','journal','journal_issue','journal_article','article','proceeding','report','module','monograph','other']) as $t)
                    <option value="{{ $t }}" {{ request('type') === $t ? 'selected' : '' }}>{{ App\Models\LibraryItem::getLocalizedType($t) }}</option>
                @endforeach
            </select>
            <select name="status" class="bg-background border border-border text-text-primary rounded-lg px-4 py-2 focus:ring-primary focus:border-primary text-sm w-32">
                <option value="">Semua Status</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Terbit</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="archived" {{ request('status') === 'archived' ? 'selected' : '' }}>Diarsipkan</option>
            </select>
            <x-button type="submit" variant="secondary">Filter</x-button>
        </form>
    </div>

    <x-table-wrapper>
        <thead>
            <tr class="border-b border-border bg-surface-hover text-xs uppercase tracking-wider text-text-muted">
                <th class="px-4 py-3 text-left font-medium">Cover</th>
                <th class="px-4 py-3 text-left font-medium">Judul</th>
                <th class="px-4 py-3 text-left font-medium">Tipe</th>
                <th class="px-4 py-3 text-left font-medium">Penulis / Penerbit</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Akses</th>
                <th class="px-4 py-3 text-left font-medium">Diperbarui</th>
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($items as $item)
                <tr class="hover:bg-surface-hover/30 transition-colors">
                    <td class="px-4 py-3">
                        @if($item->cover_path)
                            <img src="{{ Storage::url($item->cover_path) }}" alt="Cover" class="w-10 h-14 object-cover rounded shadow-sm">
                        @else
                            <div class="w-10 h-14 bg-background border border-border rounded flex items-center justify-center">
                                <svg class="w-4 h-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <strong class="block text-sm text-text-primary line-clamp-1" title="{{ $item->title }}">{{ $item->title }}</strong>
                        @if($item->isbn || $item->issn)
                            <div class="text-xs text-text-muted mt-0.5">{{ $item->isbn ?? $item->issn }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <x-badge variant="neutral">{{ App\Models\LibraryItem::getLocalizedType($item->type) }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-secondary">
                        <span class="line-clamp-2">
                            {{ $item->creators->pluck('name')->join(', ') ?: ($item->publisher ?: '-') }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <x-badge variant="{{ $item->status === 'published' ? 'success' : ($item->status === 'draft' ? 'warning' : 'neutral') }}">
                            {{ $item->status === 'published' ? 'Terbit' : ($item->status === 'draft' ? 'Draft' : 'Arsip') }}
                        </x-badge>
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $accessVariant = in_array($item->access_policy, ['public_read_download', 'public_read_only', 'registered_read_download', 'registered_read_only']) ? 'success' : 'info';
                            $accessLabel = str($item->access_policy)->replace('_', ' ')->title();
                            if ($item->access_policy === 'public_read_download') $accessLabel = 'Publik (Unduh)';
                            if ($item->access_policy === 'public_read_only') $accessLabel = 'Publik (Baca)';
                            if ($item->access_policy === 'registered_read_download') $accessLabel = 'Member (Unduh)';
                            if ($item->access_policy === 'registered_read_only') $accessLabel = 'Member (Baca)';
                            if ($item->access_policy === 'manual_purchase') $accessLabel = 'Beli Manual';
                            if ($item->access_policy === 'external') $accessLabel = 'Eksternal';
                        @endphp
                        <x-badge variant="{{ $accessVariant }}">{{ $accessLabel }}</x-badge>
                    </td>
                    <td class="px-4 py-3 text-sm text-text-secondary whitespace-nowrap">
                        {{ $item->updated_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-3 text-right whitespace-nowrap">
                        @if($item->source_type === 'legacy_book')
                            <span class="inline-flex items-center gap-1 text-xs text-text-muted bg-surface-hover px-2 py-1 rounded">
                                <svg class="w-3 h-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/></svg>
                                Dikelola melalui Buku
                            </span>
                        @else
                            <a class="text-primary hover:text-primary-hover text-sm font-semibold transition-colors" href="{{ route('admin.library.edit', $item) }}">Edit</a>
                        @endif
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="8" class="p-8">
                        <x-empty-state title="Belum ada koleksi" description="Tambahkan koleksi pertama Anda ke perpustakaan digital atau sesuaikan filter pencarian." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table-wrapper>
    <div class="mt-5">{{ $items->links() }}</div>
</x-admin-layout>
