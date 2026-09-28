<x-public-layout>
    <section class="bg-gradient-to-br from-[#111327] via-[#25164f] to-[#4c1d95] text-white">
        <div class="max-w-7xl mx-auto px-6 py-24 text-center">
            <p class="text-sm font-bold tracking-[.3em] text-violet-200">P4I DIGITAL LIBRARY</p>
            <h1 class="mt-5 text-4xl md:text-6xl font-black">Temukan Pengetahuan dalam Satu Perpustakaan.</h1>
            <p class="mt-6 text-lg text-violet-100 max-w-3xl mx-auto">Akses buku, jurnal, artikel, dan publikasi P4I dalam satu platform digital.</p>
            <form action="{{ route('library.index') }}" class="mt-10 max-w-3xl mx-auto flex rounded-2xl bg-white p-2 shadow-2xl">
                <input name="q" class="flex-1 border-0 rounded-xl text-gray-900 focus:ring-0" placeholder="Cari judul, penulis, abstrak, ISBN, ISSN, atau DOI">
                <button class="bg-violet-700 hover:bg-violet-800 px-6 py-3 rounded-xl font-bold">Cari</button>
            </form>
            <div class="mt-8 flex justify-center gap-4 flex-wrap">
                <a href="{{ route('library.index') }}" class="bg-white text-violet-900 px-6 py-3 rounded-xl font-bold">Jelajahi Perpustakaan</a>
                <a href="{{ route('submission') }}" class="border border-violet-300 px-6 py-3 rounded-xl font-bold">Terbitkan Buku</a>
            </div>
        </div>
    </section>

    @php($typeLabels = ['book'=>'Buku','journal'=>'Jurnal','journal_article'=>'Artikel Jurnal','article'=>'Artikel','proceeding'=>'Prosiding','report'=>'Laporan','module'=>'Modul','monograph'=>'Monograf','other'=>'Lainnya'])
    <div class="max-w-7xl mx-auto px-6 py-16 space-y-16">
        @foreach([['Koleksi Terbaru', $latestItems], ['Buku Pilihan', $featuredBooks], ['Jurnal & Artikel Terbaru', $latestResearch]] as [$heading, $collection])
            <section>
                <div class="flex items-end justify-between mb-6"><h2 class="text-2xl font-black dark:text-white">{{ $heading }}</h2><a class="text-violet-600 font-semibold" href="{{ route('library.index') }}">Lihat semua →</a></div>
                <div class="grid sm:grid-cols-2 lg:grid-cols-4 gap-5">
                    @forelse($collection as $item)
                        <a href="{{ route('library.show', $item) }}" class="block bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-gray-700 rounded-2xl p-5 hover:-translate-y-1 hover:shadow-lg transition">
                            <span class="text-xs font-bold uppercase text-violet-600">{{ $typeLabels[$item->type] ?? $item->type }}</span>
                            <h3 class="font-bold text-lg mt-2 dark:text-white">{{ $item->title }}</h3>
                            <p class="text-sm text-gray-500 mt-2">{{ $item->creators->pluck('name')->join(', ') ?: $item->publisher }}</p>
                        </a>
                    @empty
                        <div class="col-span-full rounded-2xl border border-dashed border-gray-300 p-8 text-center text-gray-500">Koleksi akan tampil setelah metadata diterbitkan.</div>
                    @endforelse
                </div>
            </section>
        @endforeach

        <section class="rounded-3xl bg-violet-700 text-white p-10 md:flex items-center justify-between gap-8">
            <div><h2 class="text-3xl font-black">Penerbitan Buku P4I</h2><p class="mt-3 text-violet-100">Ajukan naskah buku digital maupun cetak tanpa persyaratan KYC finansial.</p></div>
            <a href="{{ route('submission') }}" class="inline-block mt-6 md:mt-0 bg-white text-violet-800 px-6 py-3 rounded-xl font-bold">Pelajari Penerbitan</a>
        </section>
    </div>
</x-public-layout>
