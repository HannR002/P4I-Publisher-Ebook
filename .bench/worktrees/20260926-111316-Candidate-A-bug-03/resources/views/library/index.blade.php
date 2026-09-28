<x-public-layout>
    <div class="max-w-7xl mx-auto px-6 py-12">
        <div class="mb-8"><p class="text-violet-600 font-bold uppercase tracking-wider text-sm">Global Search</p><h1 class="text-4xl font-black dark:text-white">Perpustakaan Digital</h1><p class="text-gray-500 mt-2">Satu katalog untuk buku, jurnal, artikel, prosiding, laporan, modul, dan publikasi lainnya.</p></div>
        <form class="bg-white dark:bg-[#1a1d2e] border dark:border-gray-700 rounded-2xl p-5 grid md:grid-cols-4 gap-3 mb-8">
            <input name="q" value="{{ request('q') }}" placeholder="Cari seluruh koleksi..." class="md:col-span-2 rounded-xl dark:bg-[#0f1117] dark:border-gray-700">
            <select name="type" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="">Semua jenis</option>@foreach($types as $type)<option value="{{ $type }}" @selected(request('type')===$type)>{{ str($type)->replace('_',' ')->title() }}</option>@endforeach</select>
            <select name="category" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="">Semua kategori</option>@foreach($categories as $category)<option value="{{ $category->slug }}" @selected(request('category')===$category->slug)>{{ $category->name }}</option>@endforeach</select>
            <select name="access" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="">Semua akses</option><option value="free" @selected(request('access')==='free')>Gratis</option><option value="paid" @selected(request('access')==='paid')>Berbayar</option><option value="public_read" @selected(request('access')==='public_read')>Baca publik</option><option value="download_available" @selected(request('access')==='download_available')>Unduhan tersedia</option></select>
            <select name="format" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="">Semua format</option><option value="digital" @selected(request('format')==='digital')>Digital</option><option value="print" @selected(request('format')==='print')>Cetak</option></select>
            <select name="year" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="">Semua tahun</option>@foreach($years as $year)<option @selected((string)request('year')===(string)$year)>{{ $year }}</option>@endforeach</select>
            <select name="sort" class="rounded-xl dark:bg-[#0f1117] dark:border-gray-700"><option value="newest">Terbaru</option><option value="most_read" @selected(request('sort')==='most_read')>Paling dibaca</option><option value="most_downloaded" @selected(request('sort')==='most_downloaded')>Terbanyak diunduh</option><option value="trending" @selected(request('sort')==='trending')>Trending</option><option value="a-z" @selected(request('sort')==='a-z')>A–Z</option></select>
            <button class="rounded-xl bg-violet-700 text-white font-bold px-5">Terapkan</button>
        </form>
        <p class="text-sm text-gray-500 mb-4">{{ $items->total() }} koleksi ditemukan</p>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-5">
            @forelse($items as $item)
                <a href="{{ route('library.select', ['libraryItem'=>$item, 'q'=>request('q')]) }}" class="bg-white dark:bg-[#1a1d2e] border dark:border-gray-700 rounded-2xl p-6 hover:shadow-xl transition">
                    <div class="flex justify-between gap-4"><span class="text-xs uppercase font-bold text-violet-600">{{ str($item->type)->replace('_',' ') }}</span><span class="text-xs rounded-full bg-gray-100 dark:bg-gray-800 px-2 py-1">{{ str($item->access_policy)->replace('_',' ') }}</span></div>
                    <h2 class="text-xl font-bold mt-3 dark:text-white">{{ $item->title }}</h2><p class="text-sm text-gray-500 mt-2">{{ $item->creators->pluck('name')->join(', ') ?: 'P4I' }}</p><p class="text-sm text-gray-600 dark:text-gray-300 mt-4">{{ Str::limit($item->abstract ?: $item->synopsis ?: $item->description, 150) }}</p>
                </a>
            @empty
                <div class="col-span-full border border-dashed rounded-2xl p-12 text-center"><h2 class="font-bold dark:text-white">Belum ada hasil</h2><p class="text-gray-500">Coba istilah atau filter yang lebih umum.</p></div>
            @endforelse
        </div>
        <div class="mt-8">{{ $items->links() }}</div>
    </div>
</x-public-layout>
