<x-public-layout>
    <div class="max-w-5xl mx-auto px-6 py-12">
        <a href="{{ route('library.index') }}" class="text-violet-600 font-semibold">← Kembali ke perpustakaan</a>
        <article class="mt-6 bg-white dark:bg-[#1a1d2e] border dark:border-gray-700 rounded-3xl p-8 md:p-12">
            <div class="flex flex-wrap gap-2 mb-4"><span class="text-xs uppercase font-bold bg-violet-100 text-violet-800 px-3 py-1 rounded-full">{{ str($item->type)->replace('_',' ') }}</span>@foreach($item->categories as $category)<span class="text-xs bg-gray-100 dark:bg-gray-800 px-3 py-1 rounded-full">{{ $category->name }}</span>@endforeach</div>
            <h1 class="text-4xl font-black dark:text-white">{{ $item->title }}</h1><p class="mt-3 text-lg text-violet-600">{{ $item->creators->map(fn($c) => $c->name.' ('.$c->role.')')->join(', ') }}</p>
            <dl class="grid sm:grid-cols-3 gap-4 mt-8 text-sm"><div><dt class="text-gray-500">Penerbit</dt><dd class="font-semibold">{{ $item->publisher ?: '—' }}</dd></div><div><dt class="text-gray-500">Tahun</dt><dd class="font-semibold">{{ $item->publication_year ?: '—' }}</dd></div><div><dt class="text-gray-500">ISBN / ISSN / DOI</dt><dd class="font-semibold">{{ $item->isbn ?: $item->issn ?: $item->doi ?: '—' }}</dd></div></dl>
            <section class="mt-10"><h2 class="text-xl font-bold dark:text-white">{{ in_array($item->type, ['journal_article','article']) ? 'Abstract / Ringkasan Penelitian' : ($item->type === 'journal' ? 'Tentang Jurnal' : 'Tentang Buku Ini') }}</h2><div class="mt-3 leading-7 text-gray-600 dark:text-gray-300 whitespace-pre-line">{{ $item->abstract ?: $item->synopsis ?: $item->description ?: 'Deskripsi belum tersedia.' }}</div></section>
            @if($item->featured_excerpt)<section class="mt-8 border-l-4 border-violet-500 pl-5"><h2 class="font-bold">Cuplikan</h2><blockquote class="italic text-gray-600 dark:text-gray-300 mt-2">“{{ $item->featured_excerpt }}”</blockquote><p class="text-xs text-gray-500 mt-2">{{ $item->excerpt_source }} {{ $item->excerpt_page ? '— hlm. '.$item->excerpt_page : '' }}</p></section>@endif
            <div class="mt-10 flex flex-wrap gap-3">
                @if($actions['can_read'])<a href="{{ route('library.read', $item) }}" class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">{{ $actions['is_external'] ? 'Baca di Sumber / OJS' : 'Baca Sekarang' }}</a>@endif
                @if($actions['can_download'])<a href="{{ route('library.download', $item) }}" class="border border-violet-700 text-violet-700 px-6 py-3 rounded-xl font-bold">Download</a>@endif
                @if($actions['login_required'])<a href="{{ route('login') }}" class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">Masuk untuk Membaca</a>@endif
                @if($actions['can_purchase'])@auth<form method="POST" action="{{ route('manual-orders.store', $item) }}">@csrf<button class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">Beli / Dapatkan Akses</button></form>@else<a href="{{ route('login') }}" class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">Masuk untuk Membeli</a>@endauth @endif
                @if($actions['is_physical'])@auth<form method="POST" action="{{ route('manual-orders.store', $item) }}">@csrf<button class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">Pesan Buku Cetak</button></form>@else<a href="{{ route('login') }}" class="bg-violet-700 text-white px-6 py-3 rounded-xl font-bold">Masuk untuk Memesan</a>@endauth @endif
            </div>
        </article>
    </div>
</x-public-layout>
