<x-author-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('author.submissions.index') }}" class="text-text-muted hover:text-text-secondary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-text-primary leading-tight">
                {{ __('Detail & Riwayat Naskah') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto pb-12 flex flex-col lg:flex-row gap-8">

        <!-- Kolom Kiri: Info Naskah -->
        <div class="w-full lg:w-2/3 space-y-6">
            <div class="bg-surface shadow-sm rounded-xl border border-border overflow-hidden">
                <div class="px-6 py-5 border-b border-border flex justify-between items-center bg-surface-hover">
                    <h3 class="text-lg leading-6 font-medium text-text-primary">Informasi Dokumen</h3>
                    @if(in_array($submission->status, ['draft', 'revision_requested']))
                        <a href="{{ route('author.submissions.edit', $submission->id) }}" class="inline-flex items-center px-3 py-1.5 border border-border shadow-sm text-xs font-medium rounded text-text-secondary bg-background hover:bg-surface-hover transition-colors">
                            <svg class="mr-1.5 h-4 w-4 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            Edit Data
                        </a>
                    @endif
                </div>
                <div class="px-6 py-6 sm:p-8 flex flex-col sm:flex-row gap-8">
                    <!-- Cover -->
                    <div class="flex-shrink-0 w-32 h-44 rounded-md border border-border overflow-hidden shadow-sm bg-background flex items-center justify-center">
                        @if($submission->cover_preview_path)
                            <img src="{{ asset('storage/' . $submission->cover_preview_path) }}" alt="{{ $submission->title }}" class="w-full h-full object-cover">
                        @else
                            <svg class="h-10 w-10 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>

                    <!-- Meta -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h4 class="text-2xl font-bold text-text-primary">{{ $submission->title }}</h4>
                            <p class="text-sm font-medium text-primary mt-1">{{ $submission->category->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-medium text-text-muted uppercase tracking-wider">Usulan Harga</span>
                                <span class="block text-sm font-semibold text-text-primary mt-0.5">Rp {{ number_format($submission->proposed_price, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-text-muted uppercase tracking-wider">Tanggal Kirim</span>
                                <span class="block text-sm font-semibold text-text-primary mt-0.5">{{ $submission->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-text-muted uppercase tracking-wider mb-1">Sinopsis</span>
                            <p class="text-sm text-text-secondary leading-relaxed bg-surface-hover p-4 rounded-lg border border-border">
                                {{ $submission->synopsis }}
                            </p>
                        </div>
                    </div>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Status & Riwayat -->
        <div class="w-full lg:w-1/3 space-y-6">

            <!-- Status Badge -->
            <div class="bg-surface shadow-sm rounded-xl border border-border p-6 text-center">
                <h4 class="text-xs font-semibold text-text-muted uppercase tracking-wider mb-3">Status Saat Ini</h4>
                <div class="inline-flex">
                    <x-submission-status-badge :status="$submission->status" class="!text-sm !px-4 !py-2 !border" />
                </div>
            </div>

            <!-- Panel Editorial (Riwayat Review) -->
            <div class="bg-surface shadow-sm rounded-xl border border-border overflow-hidden">
                <div class="px-6 py-4 border-b border-border bg-surface-hover">
                    <h3 class="text-base font-medium text-text-primary flex items-center gap-2">
                        <svg class="h-5 w-5 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        Catatan Kurator / Editor
                    </h3>
                </div>
                <div class="p-6">
                    @if($submission->reviews->count() > 0)
                        <div class="space-y-6">
                            @foreach($submission->reviews as $review)
                                <div class="bg-surface-hover rounded-lg p-4 border border-border relative">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-xs font-bold text-primary">{{ $review->reviewer->name ?? 'Tim Editorial' }}</div>
                                        <div class="text-xs text-text-muted">{{ $review->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="text-sm text-text-secondary whitespace-pre-wrap">{{ $review->feedback }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-text-muted mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <p class="text-sm text-text-muted">Belum ada catatan atau tinjauan dari tim kurator untuk saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-author-layout>
