<div class="bg-surface border border-border rounded-2xl p-8 md:p-12 shadow-sm">
    <div class="flex flex-wrap gap-2 mb-6">
        <x-badge color="primary">{{ str($item->type)->replace('_',' ')->title() }}</x-badge>
        @foreach($item->categories as $category)
            <x-badge color="gray">{{ $category->name }}</x-badge>
        @endforeach
    </div>

    <h1 class="text-3xl sm:text-4xl font-bold text-text-primary mb-4">{{ $item->title }}</h1>

    @if($item->creators->isNotEmpty())
        <div class="text-xl text-text-secondary mb-8">
            {{ $item->creators->map(fn($c) => $c->name.' ('.$c->role.')')->join(', ') }}
        </div>
    @endif

    <dl class="grid sm:grid-cols-3 gap-6 my-10 py-6 border-y border-border">
        <div>
            <dt class="text-sm text-text-muted mb-1">Penerbit</dt>
            <dd class="font-medium text-text-primary">{{ $item->publisher ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-text-muted mb-1">Tahun Publikasi</dt>
            <dd class="font-medium text-text-primary">{{ $item->publication_year ?: '—' }}</dd>
        </div>
        <div>
            <dt class="text-sm text-text-muted mb-1">ISBN / ISSN / DOI</dt>
            <dd class="font-medium text-text-primary">{{ $item->isbn ?: $item->issn ?: $item->doi ?: '—' }}</dd>
        </div>
    </dl>

    <div class="mb-10">
        <h2 class="text-2xl font-bold text-text-primary mb-4">Informasi Publikasi</h2>
        @if($item->abstract || $item->synopsis || $item->description)
            <div class="prose dark:prose-invert max-w-none text-text-secondary leading-relaxed space-y-4">
                {!! nl2br(e($item->abstract ?: $item->synopsis ?: $item->description)) !!}
            </div>
        @else
            <x-empty-state icon="document-text" title="Tidak ada deskripsi" description="Deskripsi belum tersedia." class="border-0 shadow-none bg-transparent px-0" />
        @endif
    </div>

    @if($item->featured_excerpt)
        <div class="mb-10 bg-surface rounded-xl p-6 border-l-4 border-primary">
            <h3 class="font-bold text-text-primary mb-2">Cuplikan</h3>
            <blockquote class="italic text-text-secondary mb-2">
                “{{ $item->featured_excerpt }}”
            </blockquote>
            <p class="text-sm text-text-muted">
                {{ $item->excerpt_source }} {{ $item->excerpt_page ? '— hlm. '.$item->excerpt_page : '' }}
            </p>
        </div>
    @endif

    <div class="flex flex-wrap gap-4 mt-8 pt-8 border-t border-border">
        @if($actions['can_read'])
            <x-button tag="a" :href="route('library.read', $item)" color="primary" class="!px-6 !py-3">
                {{ $actions['is_external'] ? 'Baca di Sumber / OJS' : 'Baca Sekarang' }}
            </x-button>
        @endif
        @if($actions['can_download'])
            <x-button tag="a" :href="route('library.download', $item)" color="secondary" class="!px-6 !py-3">
                Download PDF
            </x-button>
        @endif
        @if($actions['login_required'])
            <x-button tag="a" :href="route('login')" color="primary" class="!px-6 !py-3">
                Masuk untuk Membaca
            </x-button>
        @endif
        @if($actions['can_purchase'])
            @auth
                <form method="POST" action="{{ route('manual-orders.store', $item) }}">
                    @csrf
                    <x-button type="submit" color="primary" class="!px-6 !py-3">
                        Beli / Dapatkan Akses
                    </x-button>
                </form>
            @else
                <x-button tag="a" :href="route('login')" color="primary" class="!px-6 !py-3">
                    Masuk untuk Membeli
                </x-button>
            @endauth
        @endif
        @if($actions['is_physical'])
            @auth
                <form method="POST" action="{{ route('manual-orders.store', $item) }}">
                    @csrf
                    <x-button type="submit" color="secondary" class="!px-6 !py-3 border-border">
                        Pesan Buku Cetak
                    </x-button>
                </form>
            @else
                <x-button tag="a" :href="route('login')" color="secondary" class="!px-6 !py-3 border-border">
                    Masuk untuk Memesan
                </x-button>
            @endauth
        @endif
    </div>
</div>
