<x-author-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('author.submissions.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Detail & Riwayat Naskah') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-6xl mx-auto pb-12 flex flex-col lg:flex-row gap-8">
        
        <!-- Kolom Kiri: Info Naskah -->
        <div class="w-full lg:w-2/3 space-y-6">
            <div class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Informasi Dokumen</h3>
                    @if(in_array($submission->status, ['draft', 'revision_requested']))
                        <a href="{{ route('author.submissions.edit', $submission->id) }}" class="inline-flex items-center px-3 py-1.5 border border-gray-300 dark:border-gray-600 shadow-sm text-xs font-medium rounded text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 transition-colors">
                            <svg class="mr-1.5 h-4 w-4 text-gray-500 dark:text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" /></svg>
                            Edit Data
                        </a>
                    @endif
                </div>
                <div class="px-6 py-6 sm:p-8 flex flex-col sm:flex-row gap-8">
                    <!-- Cover -->
                    <div class="flex-shrink-0 w-32 h-44 rounded-md border border-gray-200 dark:border-gray-700 overflow-hidden shadow-sm bg-gray-100 dark:bg-gray-800 flex items-center justify-center">
                        @if($submission->cover_preview_path)
                            <img src="{{ asset('storage/' . $submission->cover_preview_path) }}" alt="{{ $submission->title }}" class="w-full h-full object-cover">
                        @else
                            <svg class="h-10 w-10 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                            </svg>
                        @endif
                    </div>
                    
                    <!-- Meta -->
                    <div class="flex-1 space-y-4">
                        <div>
                            <h4 class="text-2xl font-bold text-gray-900 dark:text-white">{{ $submission->title }}</h4>
                            <p class="text-sm font-medium text-indigo-600 dark:text-indigo-400 mt-1">{{ $submission->category->name ?? 'Uncategorized' }}</p>
                        </div>
                        <div class="grid grid-cols-2 gap-4">
                            <div>
                                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usulan Harga</span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-white mt-0.5">Rp {{ number_format($submission->proposed_price, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Kirim</span>
                                <span class="block text-sm font-semibold text-gray-900 dark:text-white mt-0.5">{{ $submission->created_at->format('d M Y') }}</span>
                            </div>
                        </div>
                        <div>
                            <span class="block text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Sinopsis</span>
                            <p class="text-sm text-gray-700 dark:text-gray-300 leading-relaxed bg-gray-50 dark:bg-gray-800/50 p-4 rounded-lg border border-gray-100 dark:border-gray-700">
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
            <div class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 p-6 text-center">
                <h4 class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-3">Status Saat Ini</h4>
                @php
                    $colors = [
                        'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                        'submitted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                        'in_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
                        'revision_requested' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                        'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 border-green-200 dark:border-green-800',
                        'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                        'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 border-red-200 dark:border-red-800',
                    ];
                    $labels = [
                        'draft' => 'Draf (Belum Dikirim)',
                        'submitted' => 'Terkirim ke Editor',
                        'in_review' => 'Sedang Dikurasi',
                        'revision_requested' => 'Menunggu Revisi Penulis',
                        'approved' => 'Naskah Disetujui',
                        'published' => 'Buku Telah Terbit',
                        'rejected' => 'Naskah Ditolak',
                    ];
                @endphp
                <div class="inline-flex px-4 py-2 rounded-full border {{ $colors[$submission->status] ?? 'bg-gray-100 text-gray-800' }}">
                    <span class="font-bold text-sm">{{ $labels[$submission->status] ?? $submission->status }}</span>
                </div>
            </div>

            <!-- Panel Editorial (Riwayat Review) -->
            <div class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-200 dark:border-gray-800 bg-gray-50 dark:bg-gray-900/50">
                    <h3 class="text-base font-medium text-gray-900 dark:text-white flex items-center gap-2">
                        <svg class="h-5 w-5 text-indigo-500" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z" /></svg>
                        Catatan Kurator / Editor
                    </h3>
                </div>
                <div class="p-6">
                    @if($submission->reviews->count() > 0)
                        <div class="space-y-6">
                            @foreach($submission->reviews as $review)
                                <div class="bg-gray-50 dark:bg-gray-800/50 rounded-lg p-4 border border-gray-100 dark:border-gray-700 relative">
                                    <div class="flex items-center justify-between mb-2">
                                        <div class="text-xs font-bold text-indigo-600 dark:text-indigo-400">{{ $review->reviewer->name ?? 'Tim Editorial' }}</div>
                                        <div class="text-xs text-gray-400">{{ $review->created_at->format('d M Y H:i') }}</div>
                                    </div>
                                    <div class="text-sm text-gray-700 dark:text-gray-300 whitespace-pre-wrap">{{ $review->notes }}</div>
                                </div>
                            @endforeach
                        </div>
                    @else
                        <div class="text-center py-6">
                            <svg class="mx-auto h-12 w-12 text-gray-300 dark:text-gray-600 mb-3" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" /></svg>
                            <p class="text-sm text-gray-500 dark:text-gray-400">Belum ada catatan atau tinjauan dari tim kurator untuk saat ini.</p>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-author-layout>
