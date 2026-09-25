<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.submissions.index') }}" class="text-gray-500 hover:text-gray-700">&larr; Kembali</a>
            <h2 class="font-semibold text-xl text-gray-800 leading-tight border-l pl-4 ml-2">
                {{ __('Kurasi Naskah: ') }} <span class="font-normal">{{ $submission->title }}</span>
            </h2>
        </div>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 flex flex-col md:flex-row gap-6">
            
            <!-- Panel Kiri: Detail Naskah -->
            <div class="w-full md:w-2/3 space-y-6">
                <!-- Info -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6">
                        <div class="flex gap-6">
                            @if($submission->cover_preview_path)
                                <div class="w-32 flex-shrink-0">
                                    <img src="{{ asset('storage/'.$submission->cover_preview_path) }}" class="w-full rounded shadow" alt="Cover">
                                </div>
                            @endif
                            <div>
                                <h3 class="text-2xl font-bold">{{ $submission->title }}</h3>
                                <p class="text-indigo-600 font-semibold mb-2">{{ $submission->category->name ?? 'Uncategorized' }}</p>
                                <p class="text-sm text-gray-600 mb-1"><strong>Penulis:</strong> {{ $submission->author->pen_name }} ({{ $submission->author->user->email }})</p>
                                <p class="text-sm text-gray-600 mb-1"><strong>Usulan Harga:</strong> Rp{{ number_format($submission->proposed_price, 0, ',', '.') }}</p>
                                <p class="text-sm text-gray-600 mb-4"><strong>Tanggal Masuk:</strong> {{ $submission->created_at->format('d M Y H:i') }}</p>
                                
                                <div class="flex gap-3">
                                    <a href="{{ route('admin.submissions.stream', $submission->id) }}" target="_blank" class="bg-indigo-600 text-white px-4 py-2 rounded text-sm font-bold inline-flex items-center gap-2 hover:bg-indigo-700">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                        Baca Dokumen PDF
                                    </a>
                                </div>
                            </div>
                        </div>
                        
                        <div class="mt-6 border-t pt-4">
                            <h4 class="font-bold mb-2">Sinopsis</h4>
                            <div class="bg-gray-50 p-4 rounded text-sm text-gray-700 whitespace-pre-wrap">{{ $submission->synopsis }}</div>
                        </div>
                    </div>
                </div>
                
                <!-- Riwayat Review -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b">
                        <h4 class="font-bold">Riwayat Keputusan & Catatan Editorial</h4>
                    </div>
                    <div class="p-6">
                        @forelse($submission->reviews as $review)
                            <div class="mb-4 pb-4 border-b last:border-0 last:mb-0 last:pb-0">
                                <div class="flex justify-between items-center mb-1">
                                    <span class="text-sm font-bold text-gray-800">{{ $review->reviewer->name }}</span>
                                    <span class="text-xs text-gray-500">{{ $review->created_at->format('d M Y H:i') }}</span>
                                </div>
                                <span class="inline-block px-2 py-0.5 text-[10px] font-bold uppercase rounded bg-gray-200 text-gray-700 mb-2">{{ $review->action }}</span>
                                <p class="text-sm text-gray-600 bg-gray-50 p-3 rounded">{{ $review->notes }}</p>
                            </div>
                        @empty
                            <p class="text-sm text-gray-500 text-center italic">Belum ada catatan editorial.</p>
                        @endforelse
                    </div>
                </div>
            </div>
            
            <!-- Panel Kanan: Aksi Editorial -->
            <div class="w-full md:w-1/3 space-y-6">
                
                <!-- Status & Publish -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg border-t-4 border-indigo-500">
                    <div class="p-6">
                        <h4 class="font-bold text-gray-800 mb-2">Status Saat Ini</h4>
                        <span class="px-3 py-1 rounded-full text-sm font-bold bg-gray-100 text-gray-800">{{ strtoupper($submission->status) }}</span>
                        
                        @if(in_array($submission->status, ['submitted', 'in_review', 'revision_requested']))
                            <div class="mt-6 border-t pt-4">
                                <h4 class="font-bold text-green-700 mb-2">Terbitkan ke Katalog</h4>
                                <p class="text-xs text-gray-500 mb-3">Tindakan ini akan mengonversi naskah menjadi E-Book yang dijual publik secara permanen.</p>
                                
                                <form action="{{ route('admin.submissions.approve-publish', $submission->id) }}" method="POST" onsubmit="return confirm('Naskah akan tayang dan mulai dijual. Yakin?');">
                                    @csrf
                                    <div class="mb-3">
                                        <label class="block text-xs font-bold text-gray-700 mb-1">Harga Jual Final (Rp)</label>
                                        <input type="number" name="final_price" value="{{ $submission->proposed_price }}" required class="w-full border-gray-300 rounded text-sm focus:ring-green-500 focus:border-green-500">
                                    </div>
                                    <button type="submit" class="w-full bg-green-600 text-white font-bold py-2 px-4 rounded hover:bg-green-700">Approve & Publish</button>
                                </form>
                            </div>
                        @endif
                    </div>
                </div>
                
                <!-- Revisi & Tolak -->
                @if(in_array($submission->status, ['submitted', 'in_review', 'revision_requested']))
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg" x-data="{ mode: 'none' }">
                    <div class="p-6">
                        <h4 class="font-bold text-gray-800 mb-4">Aksi Kuratorial</h4>
                        
                        <div class="flex gap-2 mb-4">
                            <button @click="mode = 'revision'" class="flex-1 bg-orange-100 text-orange-700 font-semibold py-2 px-2 text-sm rounded hover:bg-orange-200">Minta Revisi</button>
                            <button @click="mode = 'reject'" class="flex-1 bg-red-100 text-red-700 font-semibold py-2 px-2 text-sm rounded hover:bg-red-200">Tolak Naskah</button>
                        </div>
                        
                        <!-- Form Revisi -->
                        <div x-show="mode === 'revision'" class="mt-4 p-4 border border-orange-200 bg-orange-50 rounded" x-cloak>
                            <h5 class="font-bold text-orange-800 text-sm mb-2">Minta Revisi Penulis</h5>
                            <form action="{{ route('admin.submissions.request-revision', $submission->id) }}" method="POST">
                                @csrf
                                <textarea name="feedback" rows="3" required placeholder="Jelaskan bagian mana yang perlu direvisi..." class="w-full text-sm border-gray-300 rounded mb-2"></textarea>
                                <button type="submit" class="w-full bg-orange-600 text-white text-sm font-bold py-1.5 rounded">Kirim Permintaan</button>
                            </form>
                        </div>
                        
                        <!-- Form Reject -->
                        <div x-show="mode === 'reject'" class="mt-4 p-4 border border-red-200 bg-red-50 rounded" x-cloak>
                            <h5 class="font-bold text-red-800 text-sm mb-2">Tolak Naskah Secara Permanen</h5>
                            <form action="{{ route('admin.submissions.reject', $submission->id) }}" method="POST" onsubmit="return confirm('Tolak naskah ini?');">
                                @csrf
                                <textarea name="feedback" rows="3" required placeholder="Alasan penolakan (contoh: Plagiarisme, kualitas buruk)..." class="w-full text-sm border-gray-300 rounded mb-2"></textarea>
                                <button type="submit" class="w-full bg-red-600 text-white text-sm font-bold py-1.5 rounded">Konfirmasi Penolakan</button>
                            </form>
                        </div>
                        
                    </div>
                </div>
                @endif
                
            </div>
        </div>
    </div>
</x-app-layout>
