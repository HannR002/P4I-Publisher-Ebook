<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.submissions.index') }}" class="text-text-muted hover:text-text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-text-primary">
                Kurasi Naskah: <span class="font-normal">{{ $submission->title }}</span>
            </h2>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">

        <!-- Panel Kiri: Detail Naskah -->
        <div class="w-full lg:w-2/3 space-y-6">
            <!-- Info Utama -->
            <div class="bg-surface rounded-xl border border-border overflow-hidden">
                <div class="p-6 md:p-8 flex flex-col md:flex-row gap-8">
                    @if($submission->cover_preview_path)
                        <div class="w-32 md:w-48 flex-shrink-0">
                            <img src="{{ asset('storage/'.$submission->cover_preview_path) }}" class="w-full rounded-lg shadow-sm" alt="Cover">
                        </div>
                    @endif
                    <div class="flex-1 space-y-4">
                        <div>
                            <h3 class="text-2xl font-bold text-text-primary">{{ $submission->title }}</h3>
                            <p class="text-primary font-semibold mt-1">{{ $submission->category->name ?? 'Uncategorized' }}</p>
                        </div>

                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-4 bg-background p-4 rounded-lg border border-border">
                            <div>
                                <span class="block text-xs text-text-muted uppercase tracking-wider font-semibold mb-1">Penulis</span>
                                <span class="block font-medium text-text-primary">{{ $submission->author->pen_name }}</span>
                                <span class="block text-sm text-text-secondary">{{ $submission->author->user->email }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-text-muted uppercase tracking-wider font-semibold mb-1">Usulan Harga</span>
                                <span class="block font-medium text-text-primary">Rp{{ number_format($submission->proposed_price, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs text-text-muted uppercase tracking-wider font-semibold mb-1">Tanggal Masuk</span>
                                <span class="block font-medium text-text-primary">{{ $submission->created_at->format('d M Y H:i') }}</span>
                            </div>
                        </div>

                        <div>
                            <x-button href="{{ route('admin.submissions.stream', $submission->id) }}" target="_blank" variant="primary">
                                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Baca Dokumen Naskah
                            </x-button>
                        </div>
                    </div>
                </div>

                <div class="border-t border-border p-6 md:p-8">
                    <h4 class="font-bold text-lg text-text-primary mb-3">Sinopsis</h4>
                    <div class="bg-background border border-border p-5 rounded-lg text-sm text-text-secondary whitespace-pre-wrap leading-relaxed">{{ $submission->synopsis }}</div>
                </div>
            </div>

            <!-- Riwayat Review -->
            <div class="bg-surface rounded-xl border border-border overflow-hidden">
                <div class="p-6 border-b border-border">
                    <h4 class="font-bold text-lg text-text-primary">Riwayat Keputusan & Catatan Editorial</h4>
                </div>
                <div class="p-6">
                    @forelse($submission->reviews as $review)
                        <div class="mb-6 pb-6 border-b border-border last:border-0 last:mb-0 last:pb-0">
                            <div class="flex justify-between items-start mb-3">
                                <div>
                                    <span class="block font-bold text-text-primary">{{ $review->reviewer->name }}</span>
                                    <span class="inline-flex px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-surface-hover text-text-secondary mt-1">
                                        {{ str_replace('_', ' ', $review->action) }}
                                    </span>
                                </div>
                                <span class="text-xs text-text-muted">{{ $review->created_at->format('d M Y H:i') }}</span>
                            </div>
                            <p class="text-sm text-text-secondary bg-background border border-border p-4 rounded-lg whitespace-pre-wrap">{{ $review->notes }}</p>
                        </div>
                    @empty
                        <x-empty-state title="Belum ada catatan" description="Belum ada riwayat keputusan atau catatan editorial pada naskah ini." />
                    @endforelse
                </div>
            </div>
        </div>

        <!-- Panel Kanan: Aksi Editorial -->
        <div class="w-full lg:w-1/3 space-y-6">

            <!-- Status & Publish -->
            <div class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm relative">
                <!-- Top border accent -->
                <div class="absolute top-0 left-0 w-full h-1 bg-primary"></div>

                <div class="p-6">
                    <h4 class="font-bold text-text-primary mb-3">Status Saat Ini</h4>

                    @php
                        $badgeVariant = match($submission->status) {
                            'submitted' => 'info',
                            'in_review' => 'warning',
                            'revision_requested' => 'warning',
                            'approved', 'published' => 'success',
                            'rejected' => 'error',
                            default => 'neutral'
                        };
                        if ($submission->status === 'revision_requested') $badgeVariant = 'error';

                        $statusLabel = match($submission->status) {
                            'submitted' => 'Baru Masuk',
                            'in_review' => 'Kurasi',
                            'revision_requested' => 'Menunggu Revisi',
                            'approved' => 'Disetujui',
                            'published' => 'Terbit',
                            'rejected' => 'Ditolak',
                            default => $submission->status
                        };
                    @endphp
                    <div class="mb-6">
                        <x-badge variant="{{ $badgeVariant }}">{{ $statusLabel }}</x-badge>
                    </div>

                    @if(in_array($submission->status, ['submitted', 'in_review', 'revision_requested']))
                        <div class="border-t border-border pt-6">
                            <h4 class="font-bold text-green-600 mb-2 flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                Terbitkan ke Katalog
                            </h4>
                            <p class="text-sm text-text-secondary mb-4 leading-relaxed">Setujui naskah dan konversi menjadi E-Book resmi di perpustakaan digital.</p>

                            <form action="{{ route('admin.submissions.approve-publish', $submission->id) }}" method="POST" onsubmit="return confirm('Naskah akan disetujui dan ditayangkan ke katalog publik. Anda yakin?');" class="space-y-4">
                                @csrf
                                <div>
                                    <label class="block text-sm font-bold text-text-primary mb-1">Harga Jual Final (Rp)</label>
                                    <input type="number" name="final_price" value="{{ $submission->proposed_price }}" required min="0" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-green-500 focus:border-green-500">
                                </div>
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors">
                                    Setujui & Terbitkan
                                </button>
                            </form>
                        </div>
                    @endif
                </div>
            </div>

            <!-- Revisi & Tolak -->
            @if(in_array($submission->status, ['submitted', 'in_review', 'revision_requested']))
            <div class="bg-surface rounded-xl border border-border overflow-hidden" x-data="{ mode: 'none' }">
                <div class="p-6">
                    <h4 class="font-bold text-text-primary mb-4">Aksi Kuratorial Alternatif</h4>

                    <div class="flex flex-col gap-3 mb-4">
                        <button @click="mode = mode === 'revision' ? 'none' : 'revision'"
                                :class="{'ring-2 ring-orange-500 ring-offset-2': mode === 'revision'}"
                                class="w-full bg-orange-100 text-orange-700 hover:bg-orange-200 font-semibold py-2.5 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                            Minta Revisi Penulis
                        </button>
                        <button @click="mode = mode === 'reject' ? 'none' : 'reject'"
                                :class="{'ring-2 ring-red-500 ring-offset-2': mode === 'reject'}"
                                class="w-full bg-red-100 text-red-700 hover:bg-red-200 font-semibold py-2.5 px-4 rounded-lg transition-colors flex justify-center items-center gap-2">
                            Tolak Naskah
                        </button>
                    </div>

                    <!-- Form Revisi -->
                    <div x-show="mode === 'revision'" x-collapse x-cloak>
                        <div class="p-4 border border-orange-200 bg-orange-50 rounded-lg mt-2">
                            <h5 class="font-bold text-orange-800 text-sm mb-3">Kirim Permintaan Revisi</h5>
                            <form action="{{ route('admin.submissions.request-revision', $submission->id) }}" method="POST" class="space-y-3">
                                @csrf
                                <textarea name="feedback" rows="4" required placeholder="Jelaskan bagian mana yang perlu diperbaiki (min 10 karakter)..." class="w-full bg-white border border-orange-300 rounded-lg p-3 text-sm focus:ring-orange-500 focus:border-orange-500"></textarea>
                                <button type="submit" class="w-full bg-orange-600 hover:bg-orange-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                    Kirim ke Penulis
                                </button>
                            </form>
                        </div>
                    </div>

                    <!-- Form Reject -->
                    <div x-show="mode === 'reject'" x-collapse x-cloak>
                        <div class="p-4 border border-red-200 bg-red-50 rounded-lg mt-2">
                            <h5 class="font-bold text-red-800 text-sm mb-3">Tolak Secara Permanen</h5>
                            <form action="{{ route('admin.submissions.reject', $submission->id) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin menolak naskah ini? Keputusan ini tidak dapat dibatalkan.');" class="space-y-3">
                                @csrf
                                <textarea name="feedback" rows="4" required placeholder="Berikan alasan penolakan (contoh: tidak sesuai dengan standar kualitas, plagiarisme)..." class="w-full bg-white border border-red-300 rounded-lg p-3 text-sm focus:ring-red-500 focus:border-red-500"></textarea>
                                <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                    Konfirmasi Penolakan
                                </button>
                            </form>
                        </div>
                    </div>

                </div>
            </div>
            @endif

        </div>
    </div>
</x-admin-layout>
