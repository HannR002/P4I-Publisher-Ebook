<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-text-primary">Admin Dashboard</h2>
    </x-slot>

    <div class="space-y-8">
        <!-- Executive Summary Metrics -->
        <section>
            <h3 class="text-lg font-semibold text-text-primary mb-4">Ringkasan Eksekutif</h3>
            <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                <div class="bg-surface rounded-xl p-5 border border-border">
                    <p class="text-sm font-medium text-text-secondary">Total Koleksi</p>
                    <p class="text-3xl font-black mt-2 text-text-primary">{{ number_format($inventory['total']) }}</p>
                    <div class="mt-2 text-xs text-text-muted flex gap-2">
                        <span>Buku: {{ $inventory['books'] }}</span>
                        <span>Jurnal: {{ $inventory['journals'] }}</span>
                    </div>
                </div>
                <div class="bg-surface rounded-xl p-5 border border-border">
                    <p class="text-sm font-medium text-text-secondary">Publikasi Baru (30 Hari)</p>
                    <p class="text-3xl font-black mt-2 text-text-primary">{{ number_format($inventory['new_30']) }}</p>
                </div>
                <div class="bg-surface rounded-xl p-5 border border-border">
                    <p class="text-sm font-medium text-text-secondary">Dibaca / Diunduh Hari Ini</p>
                    <p class="text-3xl font-black mt-2 text-text-primary">
                        {{ number_format($engagement['reads_today']) }} <span class="text-lg font-normal text-text-muted">/ {{ number_format($engagement['downloads_today']) }}</span>
                    </p>
                </div>
                <div class="bg-surface rounded-xl p-5 border border-border">
                    <p class="text-sm font-medium text-text-secondary">Pembayaran Menunggu Verifikasi</p>
                    <p class="text-3xl font-black mt-2 {{ $actions['pending_payments'] > 0 ? 'text-primary' : 'text-text-primary' }}">
                        {{ number_format($actions['pending_payments']) }}
                    </p>
                </div>
            </div>
        </section>

        <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
            <!-- Left Column: Actions & Publishing -->
            <div class="lg:col-span-2 space-y-8">

                <!-- Perlu Tindakan -->
                <section>
                    <h3 class="text-lg font-semibold text-text-primary mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                        Perlu Tindakan
                    </h3>

                    @php
                        $hasActions = $actions['pending_payments'] > 0 || $actions['pending_reviews'] > 0 || $actions['needs_revision'] > 0 || $actions['missing_cover'] > 0 || $actions['missing_file'] > 0;
                    @endphp

                    <div class="bg-surface rounded-xl border border-border overflow-hidden">
                        @if(!$hasActions)
                            <div class="p-8 text-center">
                                <svg class="w-12 h-12 text-primary/40 mx-auto mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                <p class="text-text-secondary font-medium">Tidak ada tindakan mendesak.</p>
                            </div>
                        @else
                            <ul class="divide-y divide-border">
                                @if($actions['pending_payments'] > 0)
                                    <li>
                                        <a href="{{ route('admin.payments.index') }}" class="flex items-center justify-between p-4 hover:bg-surface-hover transition-colors">
                                            <span class="font-medium text-text-primary">Pembayaran menunggu verifikasi</span>
                                            <span class="inline-flex items-center justify-center bg-primary text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $actions['pending_payments'] }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if($actions['pending_reviews'] > 0)
                                    <li>
                                        <a href="{{ route('admin.submissions.index') }}?status=in_review" class="flex items-center justify-between p-4 hover:bg-surface-hover transition-colors">
                                            <span class="font-medium text-text-primary">Naskah sedang menunggu review</span>
                                            <span class="inline-flex items-center justify-center bg-yellow-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $actions['pending_reviews'] }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if($actions['needs_revision'] > 0)
                                    <li>
                                        <a href="{{ route('admin.submissions.index') }}?status=revision_requested" class="flex items-center justify-between p-4 hover:bg-surface-hover transition-colors">
                                            <span class="font-medium text-text-primary">Naskah memerlukan tindakan editor / revisi</span>
                                            <span class="inline-flex items-center justify-center bg-orange-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $actions['needs_revision'] }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if($actions['missing_cover'] > 0)
                                    <li>
                                        <a href="{{ route('admin.library.index') }}?missing=cover" class="flex items-center justify-between p-4 hover:bg-surface-hover transition-colors">
                                            <span class="font-medium text-text-primary">Publikasi tidak memiliki cover</span>
                                            <span class="inline-flex items-center justify-center bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $actions['missing_cover'] }}</span>
                                        </a>
                                    </li>
                                @endif
                                @if($actions['missing_file'] > 0)
                                    <li>
                                        <a href="{{ route('admin.library.index') }}?missing=file" class="flex items-center justify-between p-4 hover:bg-surface-hover transition-colors">
                                            <span class="font-medium text-text-primary">Publikasi tidak memiliki file / sumber akses</span>
                                            <span class="inline-flex items-center justify-center bg-red-500 text-white text-xs font-bold px-2.5 py-1 rounded-full">{{ $actions['missing_file'] }}</span>
                                        </a>
                                    </li>
                                @endif
                            </ul>
                        @endif
                    </div>
                </section>

                <!-- Recent Collection Activity -->
                <section>
                    <h3 class="text-lg font-semibold text-text-primary mb-4">Koleksi Terbaru</h3>
                    <div class="bg-surface rounded-xl border border-border overflow-hidden">
                        <table class="w-full text-left border-collapse">
                            <thead>
                                <tr class="bg-surface-hover border-b border-border text-xs uppercase tracking-wider text-text-muted">
                                    <th class="px-4 py-3 font-medium">Tipe</th>
                                    <th class="px-4 py-3 font-medium">Judul</th>
                                    <th class="px-4 py-3 font-medium">Ditambahkan</th>
                                </tr>
                            </thead>
                            <tbody class="divide-y divide-border">
                                @forelse($recentCollections as $item)
                                    <tr class="hover:bg-surface-hover/50">
                                        <td class="px-4 py-3 text-sm text-text-secondary">
                                            {{ App\Models\LibraryItem::getLocalizedType($item->type ?? 'book') }}
                                        </td>
                                        <td class="px-4 py-3">
                                            <a href="{{ route('admin.library.edit', $item) }}" class="text-sm font-medium text-primary hover:underline line-clamp-1">
                                                {{ $item->title }}
                                            </a>
                                            <p class="text-xs text-text-muted line-clamp-1">{{ $item->publisher ?? $item->creators->first()?->name ?? '-' }}</p>
                                        </td>
                                        <td class="px-4 py-3 text-sm text-text-secondary whitespace-nowrap">
                                            {{ $item->created_at->diffForHumans() }}
                                        </td>
                                    </tr>
                                @empty
                                    <tr>
                                        <td colspan="3" class="px-4 py-8 text-center text-text-muted">Belum ada koleksi</td>
                                    </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </section>
            </div>

            <!-- Right Column: Publishing, Inventory & Analytics Previews -->
            <div class="space-y-8">

                <!-- Publishing Pipeline -->
                <section>
                    <h3 class="text-lg font-semibold text-text-primary mb-4">Status Penerbitan</h3>
                    <div class="bg-surface rounded-xl p-5 border border-border space-y-3">
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-text-secondary">Naskah Baru (Diajukan)</span>
                            <span class="font-bold text-text-primary">{{ $publishing['submitted'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-primary font-medium">Sedang Direview</span>
                            <span class="font-bold text-primary">{{ $publishing['in_review'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-orange-500 font-medium">Perlu Revisi</span>
                            <span class="font-bold text-orange-500">{{ $publishing['revision_requested'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm pt-2 border-t border-border">
                            <span class="text-text-secondary">Disetujui</span>
                            <span class="font-bold text-text-primary">{{ $publishing['approved'] }}</span>
                        </div>
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-text-secondary">Terbit</span>
                            <span class="font-bold text-text-primary">{{ $publishing['published'] }}</span>
                        </div>
                    </div>
                </section>

                <!-- Inventory By Type -->
                <section>
                    <h3 class="text-lg font-semibold text-text-primary mb-4">Inventaris per Jenis</h3>
                    <div class="bg-surface rounded-xl p-5 border border-border space-y-3">
                        @foreach($inventory['by_type'] as $type => $total)
                            <div class="flex justify-between items-center text-sm">
                                <span class="text-text-secondary">{{ App\Models\LibraryItem::getLocalizedType($type) }}</span>
                                <span class="font-bold text-text-primary">{{ $total }}</span>
                            </div>
                        @endforeach
                    </div>
                </section>

                <!-- Search Demand Preview -->
                <section>
                    <h3 class="text-lg font-semibold text-text-primary mb-4">Kebutuhan Pencarian</h3>
                    <div class="bg-surface rounded-xl p-5 border border-border">
                        <h4 class="text-sm font-bold text-text-primary mb-2">Pencarian tanpa hasil:</h4>
                        @if($zeroSearches->isEmpty())
                            <p class="text-sm text-text-muted italic">Belum ada data pencarian.</p>
                        @else
                            <ul class="space-y-2">
                                @foreach($zeroSearches as $search)
                                    <li class="flex justify-between text-sm">
                                        <span class="text-text-secondary truncate pr-2">"{{ $search->search_term }}"</span>
                                        <span class="text-text-muted whitespace-nowrap">{{ $search->total }} kali</span>
                                    </li>
                                @endforeach
                            </ul>
                        @endif
                    </div>
                </section>

            </div>

            <!-- Feature Status -->
            <section class="mt-8">
                <h3 class="text-lg font-semibold text-text-primary mb-4">Status Fitur Sistem</h3>
                <div class="bg-surface rounded-xl p-5 border border-border space-y-3">
                    @foreach($featureStatus as $feature => $status)
                        <div class="flex justify-between items-center text-sm">
                            <span class="text-text-secondary">{{ $feature }}</span>
                            <span class="font-bold {{ $status == 'Aktif' ? 'text-green-600' : 'text-text-muted' }}">
                                {{ $status }}
                            </span>
                        </div>
                    @endforeach
                </div>
            </section>

        </div>
    </div>
</x-admin-layout>
