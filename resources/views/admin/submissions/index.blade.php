<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-text-primary">Kurasi Naskah Masuk</h2>
    </x-slot>

    <!-- Filters -->
    <div class="mb-6 flex flex-wrap gap-2">
        @php
            $filters = [
                'all' => 'Semua',
                'submitted' => 'Baru Masuk',
                'in_review' => 'Proses Kurasi',
                'revision_requested' => 'Menunggu Revisi',
                'approved' => 'Disetujui',
                'published' => 'Terbit',
                'rejected' => 'Ditolak'
            ];
        @endphp
        @foreach($filters as $key => $label)
            <a href="{{ route('admin.submissions.index', ['status' => $key]) }}" class="px-4 py-2 text-sm rounded-full transition-colors {{ $status == $key ? 'bg-primary text-white font-medium' : 'bg-surface border border-border text-text-secondary hover:text-text-primary hover:bg-surface-hover' }}">
                {{ $label }}
            </a>
        @endforeach
    </div>

    <x-table-wrapper>
        <thead>
            <tr class="border-b border-border bg-surface-hover text-xs uppercase tracking-wider text-text-muted">
                <th class="px-4 py-3 text-left font-medium">Judul & Penulis</th>
                <th class="px-4 py-3 text-left font-medium">Kategori</th>
                <th class="px-4 py-3 text-left font-medium">Usulan Harga</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Tanggal Masuk</th>
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($submissions as $sub)
                <tr class="hover:bg-surface-hover/30 transition-colors">
                    <td class="px-4 py-4">
                        <strong class="block text-sm text-text-primary line-clamp-1" title="{{ $sub->title }}">{{ $sub->title }}</strong>
                        <div class="text-xs text-text-muted mt-0.5">Oleh: {{ $sub->author->pen_name ?? '-' }}</div>
                    </td>
                    <td class="px-4 py-4 text-sm text-text-secondary">
                        {{ $sub->category->name ?? '-' }}
                    </td>
                    <td class="px-4 py-4 text-sm text-text-secondary whitespace-nowrap">
                        Rp{{ number_format($sub->proposed_price, 0, ',', '.') }}
                    </td>
                    <td class="px-4 py-4">
                        @php
                            $badgeVariant = match($sub->status) {
                                'submitted' => 'info',
                                'in_review' => 'warning',
                                'revision_requested' => 'warning', // wait, let's use a custom color if available or warning
                                'approved', 'published' => 'success',
                                'rejected' => 'error',
                                default => 'neutral'
                            };
                            if ($sub->status === 'revision_requested') $badgeVariant = 'error'; // make it stand out

                            $statusLabel = match($sub->status) {
                                'submitted' => 'Baru Masuk',
                                'in_review' => 'Kurasi',
                                'revision_requested' => 'Revisi',
                                'approved' => 'Disetujui',
                                'published' => 'Terbit',
                                'rejected' => 'Ditolak',
                                default => $sub->status
                            };
                        @endphp
                        <x-badge variant="{{ $badgeVariant }}">{{ $statusLabel }}</x-badge>
                    </td>
                    <td class="px-4 py-4 text-sm text-text-secondary whitespace-nowrap">
                        {{ $sub->created_at->format('d M Y') }}
                    </td>
                    <td class="px-4 py-4 text-right whitespace-nowrap">
                        <x-button type="button" href="{{ route('admin.submissions.show', $sub->id) }}" variant="secondary" size="sm">
                            Lihat Detail
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="6" class="p-8">
                        <x-empty-state title="Belum ada naskah" description="Belum ada naskah masuk untuk dikurasi." />
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table-wrapper>

    <div class="mt-5">
        {{ $submissions->links() }}
    </div>
</x-admin-layout>
