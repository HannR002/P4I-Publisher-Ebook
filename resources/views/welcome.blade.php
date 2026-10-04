<x-public-layout>
    <!-- Hero Section -->
    <section class="bg-surface border-b border-border text-text-primary">
        <div class="max-w-7xl mx-auto px-6 py-20 text-center">
            <p class="text-sm font-bold tracking-widest text-text-secondary uppercase">P4I Digital Library</p>
            <h1 class="mt-5 text-4xl md:text-5xl lg:text-6xl font-black tracking-tight">Temukan Pengetahuan dalam Satu Perpustakaan.</h1>
            <p class="mt-6 text-lg text-text-secondary max-w-3xl mx-auto">Akses buku, jurnal, artikel, dan publikasi P4I dalam satu platform digital.</p>

            <form action="{{ route('library.index') }}" class="mt-10 max-w-3xl mx-auto flex items-center bg-background border border-border-strong rounded-2xl p-2 shadow-sm focus-within:ring-2 focus-within:ring-primary transition-all">
                <div class="px-4 text-text-muted">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input name="q" class="flex-1 bg-transparent border-0 text-text-primary focus:ring-0 placeholder-text-muted text-base lg:text-lg" placeholder="Cari buku, jurnal, artikel, penulis, DOI, ISBN...">
                <x-button type="submit" variant="primary" class="px-6 py-3 rounded-xl font-bold">Cari</x-button>
            </form>

            <div class="mt-8 flex justify-center gap-4 flex-wrap">
                <x-button href="{{ route('library.index') }}" variant="secondary" class="px-6 py-3 rounded-xl font-bold">Jelajahi Perpustakaan</x-button>
                <x-button href="{{ route('submission') }}" variant="ghost" class="px-6 py-3 rounded-xl font-bold">Terbitkan Buku</x-button>
            </div>
        </div>
    </section>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-6 py-16 space-y-16">

        <!-- Koleksi Terbaru -->
        <section>
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl font-black text-text-primary">Koleksi Terbaru</h2>
                <a class="text-primary hover:text-primary-hover font-semibold transition-colors focus-ring rounded-lg px-2 py-1" href="{{ route('library.index') }}">Lihat semua &rarr;</a>
            </div>

            @if(count($latestItems) > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($latestItems as $item)
                        <x-publication-card :item="$item" :url="route('library.show', $item)" />
                    @endforeach
                </div>
            @else
                <x-empty-state title="Belum ada koleksi" description="Koleksi akan tampil setelah metadata diterbitkan." />
            @endif
        </section>

        <!-- Buku Pilihan -->
        <section>
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl font-black text-text-primary">Buku Pilihan</h2>
                <a class="text-primary hover:text-primary-hover font-semibold transition-colors focus-ring rounded-lg px-2 py-1" href="{{ route('library.index', ['type' => 'book']) }}">Lihat semua buku &rarr;</a>
            </div>

            @if(count($featuredBooks) > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($featuredBooks as $item)
                        <x-publication-card :item="$item" :url="route('library.show', $item)" />
                    @endforeach
                </div>
            @else
                <x-empty-state title="Belum ada buku" description="Buku akan tampil setelah metadata diterbitkan." />
            @endif
        </section>

        <!-- Jurnal & Artikel Terbaru -->
        <section>
            <div class="flex items-end justify-between mb-6">
                <h2 class="text-2xl font-black text-text-primary">Jurnal & Artikel Terbaru</h2>
                <a class="text-primary hover:text-primary-hover font-semibold transition-colors focus-ring rounded-lg px-2 py-1" href="{{ route('library.index', ['type' => 'journal_article']) }}">Lihat semua artikel &rarr;</a>
            </div>

            @if(count($latestResearch) > 0)
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-6">
                    @foreach($latestResearch as $item)
                        <x-publication-card :item="$item" :url="route('library.show', $item)" />
                    @endforeach
                </div>
            @else
                <x-empty-state title="Belum ada artikel" description="Jurnal dan artikel akan tampil setelah metadata diterbitkan." />
            @endif
        </section>

        <!-- Call to Action: Penerbitan -->
        <section class="rounded-3xl bg-primary-subtle border border-primary/20 p-10 md:flex items-center justify-between gap-8 shadow-sm">
            <div>
                <h2 class="text-3xl font-black text-text-primary">Penerbitan Buku P4I</h2>
                <p class="mt-3 text-text-secondary">Ajukan naskah buku digital maupun cetak dengan cepat dan mudah ke penerbit P4I.</p>
            </div>
            <div class="mt-6 md:mt-0 flex-shrink-0">
                <x-button href="{{ route('submission') }}" variant="primary" class="px-6 py-3 rounded-xl font-bold">Pelajari Penerbitan</x-button>
            </div>
        </section>
    </div>
</x-public-layout>
