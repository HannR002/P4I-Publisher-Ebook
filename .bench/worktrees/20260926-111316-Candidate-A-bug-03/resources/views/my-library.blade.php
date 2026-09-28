<x-public-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
            <div>
                <p class="text-sm font-bold text-blue-600 dark:text-indigo-400 uppercase tracking-widest mb-2">Portal Pembaca</p>
                <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">Perpustakaan Saya</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2 font-medium">Koleksi e-book yang telah Anda miliki — akses selamanya.</p>
            </div>
            <div class="flex items-center gap-3 shrink-0">
                <a href="{{ route('my-orders') }}" class="inline-flex items-center gap-2 text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors px-4 py-2 rounded-full border border-gray-200 dark:border-[#2d3147] hover:border-gray-400 dark:hover:border-gray-500 hover:bg-gray-50 dark:hover:bg-[#1a1d2e]">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"></path></svg>
                    Riwayat Transaksi
                </a>
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 text-sm font-bold text-white dark:text-gray-900 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 px-5 py-2.5 rounded-full transition-all hover:-translate-y-0.5 hover:shadow-lg">
                    + Tambah Buku
                </a>
            </div>
        </div>

        @if($licenses->isEmpty())
            {{-- ─── EMPTY STATE ─────────────────────────────────────────────── --}}
            <div class="flex flex-col items-center justify-center py-28 text-center">
                <div class="w-24 h-24 bg-gray-100 rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 mb-2">Perpustakaan Anda Masih Kosong</h2>
                <p class="text-gray-500 max-w-sm mb-8 leading-relaxed">Anda belum memiliki buku. Telusuri katalog kami dan temukan bacaan pertama Anda hari ini.</p>
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-full text-base font-bold text-white bg-black hover:bg-gray-800 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/></svg>
                    Telusuri Katalog
                </a>
            </div>
        @else
            {{-- ─── STATS SUMMARY ───────────────────────────────────────────── --}}
            <div class="grid grid-cols-2 sm:grid-cols-4 gap-4 mb-10">
                <div class="bg-white dark:bg-[#1a1d2e] rounded-2xl border border-gray-100 dark:border-[#2d3147] shadow-sm p-5 flex items-center gap-4 transition-colors">
                    <div class="w-10 h-10 bg-blue-50 dark:bg-indigo-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-blue-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $licenses->count() }}</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Buku Dimiliki</p>
                    </div>
                </div>
                <div class="bg-white dark:bg-[#1a1d2e] rounded-2xl border border-gray-100 dark:border-[#2d3147] shadow-sm p-5 flex items-center gap-4 transition-colors">
                    <div class="w-10 h-10 bg-emerald-50 dark:bg-emerald-900/30 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                    </div>
                    <div>
                        <p class="text-2xl font-black text-gray-900 dark:text-white">∞</p>
                        <p class="text-xs text-gray-500 dark:text-gray-400 font-medium">Masa Akses</p>
                    </div>
                </div>
                <div class="col-span-2 bg-gradient-to-r from-blue-600 to-blue-700 dark:from-indigo-600 dark:to-indigo-800 rounded-2xl p-5 flex items-center gap-4">
                    <div class="w-10 h-10 bg-white/20 rounded-xl flex items-center justify-center shrink-0">
                        <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    </div>
                    <div>
                        <p class="text-lg font-extrabold text-white">Lisensi DRM Aktif</p>
                        <p class="text-xs text-blue-200 dark:text-indigo-200 font-medium">Dilindungi enkripsi, baca di mana saja</p>
                    </div>
                </div>
            </div>

            {{-- ─── BOOK GRID ───────────────────────────────────────────────── --}}
            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-8">
                @foreach($licenses as $license)
                    @if($license->book)
                        <div class="group flex flex-col bg-white dark:bg-[#161615] rounded-3xl border border-gray-100 dark:border-[#2d3147] shadow-sm hover:shadow-xl hover:-translate-y-2 transition-all duration-500">
                            {{-- Cover --}}
                            <div class="relative px-5 pt-5 pb-2">
                                <div class="aspect-[2/3] w-full rounded-2xl overflow-hidden bg-gray-50 dark:bg-[#1a1d2e] shadow-md group-hover:shadow-xl transition-shadow duration-500">
                                    @if($license->book->cover_image_path)
                                        <img
                                            src="{{ $license->book->cover_image_path }}"
                                            alt="Cover {{ $license->book->title }}"
                                            class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out"
                                        >
                                    @else
                                        <div class="w-full h-full flex flex-col items-center justify-center bg-gradient-to-br from-gray-100 to-gray-200 dark:from-[#1a1d2e] dark:to-[#12141f]">
                                            <svg class="w-10 h-10 text-gray-300 dark:text-gray-600 mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        </div>
                                    @endif
                                </div>
                                {{-- "Dimiliki" badge --}}
                                <div class="absolute top-7 right-7 bg-emerald-500 text-white text-[9px] font-extrabold px-2.5 py-1 rounded-full shadow-md uppercase tracking-widest border border-emerald-400">
                                    Milik Saya
                                </div>
                            </div>

                            {{-- Info --}}
                            <div class="px-5 pb-5 pt-2 flex flex-col flex-grow">
                                <div class="mb-4 flex-grow">
                                    @if($license->book->author)
                                        <p class="text-[11px] font-bold text-gray-400 dark:text-gray-500 uppercase tracking-widest mb-1">{{ $license->book->author }}</p>
                                    @endif
                                    <h3 class="text-base font-extrabold text-gray-900 dark:text-white leading-snug group-hover:text-blue-600 dark:group-hover:text-indigo-400 transition-colors line-clamp-2">
                                        {{ $license->book->title }}
                                    </h3>
                                </div>

                                {{-- CTA: Baca Sekarang --}}
                                <a
                                    href="{{ route('drm.reader', $license->book->id) }}"
                                    data-testid="btn-read-book-{{ $license->book->id }}"
                                    class="w-full flex justify-center items-center gap-2 py-3 px-4 rounded-xl text-sm font-extrabold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 hover:text-emerald-800 transition-colors shadow-sm group/btn border border-emerald-200 dark:border-emerald-800"
                                >
                                    {{-- BookOpen icon --}}
                                    <svg class="w-4 h-4 group-hover/btn:scale-110 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/>
                                    </svg>
                                    Baca Sekarang
                                </a>
                            </div>
                        </div>
                    @endif
                @endforeach
            </div>
        @endif
    </div>
</x-public-layout>
