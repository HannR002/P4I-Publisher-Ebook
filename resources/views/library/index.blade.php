<x-public-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Header -->
        <div class="mb-8 border-b border-border pb-6">
            <p class="text-primary font-bold uppercase tracking-wider text-sm mb-2">Global Search</p>
            <h1 class="text-3xl md:text-4xl font-black text-text-primary tracking-tight">Katalog Perpustakaan</h1>
            <p class="text-text-secondary mt-3 max-w-2xl text-lg">Satu akses untuk buku, jurnal, artikel, prosiding, laporan, modul, dan publikasi ilmiah lainnya.</p>
        </div>

        <div class="flex flex-col lg:flex-row gap-8" x-data="{ showMobileFilters: false }">
            <!-- Mobile Filter Toggle -->
            <div class="lg:hidden flex items-center justify-between mb-4">
                <p class="text-sm font-semibold text-text-secondary">{{ $items->total() }} koleksi ditemukan</p>
                <x-button @click="showMobileFilters = !showMobileFilters" variant="secondary" class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 4a1 1 0 011-1h16a1 1 0 011 1v2.586a1 1 0 01-.293.707l-6.414 6.414a1 1 0 00-.293.707V17l-4 4v-6.586a1 1 0 00-.293-.707L3.293 7.293A1 1 0 013 6.586V4z"></path></svg>
                    Filter & Urutkan
                </x-button>
            </div>

            <!-- Filters Sidebar -->
            <div :class="showMobileFilters ? 'block' : 'hidden lg:block'" class="w-full lg:w-1/4 flex-shrink-0">
                <form action="{{ route('library.index') }}" method="GET" class="sticky top-24 space-y-6 bg-surface p-5 rounded-2xl border border-border shadow-sm">
                    <div class="flex items-center justify-between border-b border-border pb-4">
                        <h3 class="font-bold text-lg text-text-primary">Filter</h3>
                        @if(request()->except('page'))
                            <a href="{{ route('library.index') }}" class="text-sm font-medium text-danger hover:underline">Reset</a>
                        @endif
                    </div>

                    <div class="space-y-4">
                        <x-form-field id="q" name="q" label="Kata Kunci" :value="request('q')" placeholder="Cari..." />

                        <x-form-field id="type" name="type" type="select" label="Jenis Konten" :value="request('type')" :options="['' => 'Semua Jenis'] + collect($types)->mapWithKeys(fn($t) => [$t => \App\Models\LibraryItem::getLocalizedType($t)])->all()" />

                        <x-form-field id="category" name="category" type="select" label="Kategori" :value="request('category')" :options="['' => 'Semua Kategori'] + $categories->pluck('name', 'slug')->all()" />

                        <x-form-field id="access" name="access" type="select" label="Akses" :value="request('access')" :options="['' => 'Semua Akses', 'free' => 'Gratis', 'paid' => 'Berbayar', 'public_read' => 'Baca Publik', 'download_available' => 'Bisa Diunduh']" />

                        <x-form-field id="format" name="format" type="select" label="Format" :value="request('format')" :options="['' => 'Semua Format', 'digital' => 'Digital', 'print' => 'Cetak']" />

                        <x-form-field id="year" name="year" type="select" label="Tahun" :value="request('year')" :options="['' => 'Semua Tahun'] + collect($years)->mapWithKeys(fn($y) => [$y => $y])->all()" />

                        <div class="border-t border-border pt-4">
                            <x-form-field id="sort" name="sort" type="select" label="Urutkan" :value="request('sort', 'newest')" :options="['newest' => 'Terbaru', 'most_read' => 'Paling Banyak Dibaca', 'most_downloaded' => 'Paling Banyak Diunduh', 'trending' => 'Sedang Tren', 'a-z' => 'A-Z']" />
                        </div>
                    </div>

                    <div class="pt-2">
                        <x-button type="submit" variant="primary" class="w-full justify-center">Terapkan Filter</x-button>
                    </div>
                </form>
            </div>

            <!-- Results Area -->
            <div class="w-full lg:w-3/4">
                <div class="hidden lg:flex items-center justify-between mb-6">
                    <p class="text-sm font-semibold text-text-secondary">{{ $items->total() }} koleksi ditemukan</p>

                    @if(request('q'))
                        <div class="flex items-center gap-2 text-sm text-text-secondary bg-surface border border-border px-3 py-1.5 rounded-full">
                            <span>Pencarian:</span>
                            <span class="font-bold text-text-primary">"{{ request('q') }}"</span>
                        </div>
                    @endif
                </div>

                <div class="grid grid-cols-2 md:grid-cols-3 gap-4 lg:gap-6">
                    @forelse($items as $item)
                        <x-publication-card
                            :item="$item"
                            :url="route('library.select', ['libraryItem'=>$item, 'q'=>request('q')])"
                        />
                    @empty
                        <div class="col-span-full py-12">
                            <x-empty-state
                                title="Belum ada koleksi yang cocok dengan pencarian ini."
                                description="Coba gunakan filter yang lebih umum atau periksa ejaan kata kunci Anda."
                            >
                                <x-slot name="icon">
                                    <svg class="w-8 h-8 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                                </x-slot>
                                <div class="mt-6">
                                    <x-button href="{{ route('library.index') }}" variant="secondary">Hapus Semua Filter</x-button>
                                </div>
                            </x-empty-state>
                        </div>
                    @endforelse
                </div>

                <!-- Pagination -->
                @if($items->hasPages())
                    <div class="mt-10 bg-surface border border-border p-4 rounded-xl shadow-sm">
                        {{ $items->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-public-layout>
