<div class="grid grid-cols-1 lg:grid-cols-12 gap-12">
    <!-- Cover Column -->
    <div class="lg:col-span-4 flex justify-center lg:justify-start">
        @if($item->cover_image)
            <img src="{{ Storage::url($item->cover_image) }}" alt="Sampul {{ $item->title }}" class="w-full max-w-xs sm:max-w-sm rounded-xl shadow-lg border border-border object-cover aspect-[2/3]">
        @else
            <div class="w-full max-w-xs sm:max-w-sm rounded-xl shadow border border-border bg-surface flex items-center justify-center aspect-[2/3]">
                <svg class="h-24 w-24 text-text-muted opacity-50" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                </svg>
            </div>
        @endif
    </div>

    <!-- Details Column -->
    <div class="lg:col-span-8">
        <div class="flex flex-wrap gap-2 mb-4">
            <x-badge color="primary">{{ str($item->type)->replace('_', ' ')->title() }}</x-badge>
            @foreach($item->categories as $category)
                <x-badge color="gray">{{ $category->name }}</x-badge>
            @endforeach
        </div>

        <h1 class="text-3xl sm:text-4xl font-bold text-text-primary mb-4 leading-tight">
            {{ $item->title }}
        </h1>

        <div class="mb-8 text-lg text-text-secondary">
            @if($item->creators->isNotEmpty())
                Oleh <span class="font-medium text-text-primary">{{ $item->creators->map(fn($c) => $c->name)->join(', ') }}</span>
            @else
                Penulis Tidak Diketahui
            @endif
        </div>

        <!-- Metadata Grid -->
        <dl class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-4 gap-6 mb-10 py-6 border-y border-border">
            <div>
                <dt class="text-sm text-text-muted mb-1">Penerbit</dt>
                <dd class="font-medium text-text-primary">{{ $item->publisher ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-muted mb-1">Tahun Terbit</dt>
                <dd class="font-medium text-text-primary">{{ $item->publication_year ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-muted mb-1">ISBN</dt>
                <dd class="font-medium text-text-primary">{{ $item->isbn ?: '—' }}</dd>
            </div>
            <div>
                <dt class="text-sm text-text-muted mb-1">Bahasa</dt>
                <dd class="font-medium text-text-primary">{{ $item->language ? str($item->language)->upper() : '—' }}</dd>
            </div>
        </dl>

        <!-- Actions -->
        <div class="flex flex-wrap gap-4 mb-12">
            @if($actions['can_read'])
                <x-button tag="a" :href="route('library.read', $item)" color="primary" class="!px-8 !py-3">
                    {{ $actions['is_external'] ? 'Baca di Sumber / OJS' : 'Baca Sekarang' }}
                </x-button>
            @endif
            @if($actions['can_download'])
                <x-button tag="a" :href="route('library.download', $item)" color="secondary" class="!px-8 !py-3">
                    Download PDF
                </x-button>
            @endif
            @if($actions['login_required'])
                <x-button tag="a" :href="route('login')" color="primary" class="!px-8 !py-3">
                    Masuk untuk Membaca
                </x-button>
            @endif

            @if($actions['can_purchase'])
                @auth
                    <form method="POST" action="{{ route('manual-orders.store', $item) }}">
                        @csrf
                        <x-button type="submit" color="primary" class="!px-8 !py-3">
                            Beli / Dapatkan Akses
                        </x-button>
                    </form>
                @else
                    <x-button tag="a" :href="route('login')" color="primary" class="!px-8 !py-3">
                        Masuk untuk Membeli
                    </x-button>
                @endauth
            @endif

            @if($actions['is_physical'])
                @auth
                    <form method="POST" action="{{ route('manual-orders.store', $item) }}">
                        @csrf
                        <x-button type="submit" color="secondary" class="!px-8 !py-3 border-border">
                            Pesan Buku Cetak
                        </x-button>
                    </form>
                @else
                    <x-button tag="a" :href="route('login')" color="secondary" class="!px-8 !py-3 border-border">
                        Masuk untuk Memesan
                    </x-button>
                @endauth
            @endif
        </div>

        <!-- Formats Available -->
        @if($item->editions && $item->editions->isNotEmpty())
            <div class="mb-12">
                <h2 class="text-xl font-semibold text-text-primary mb-4">Format Tersedia</h2>
                <div class="flex flex-col sm:flex-row gap-4">
                    @foreach($item->editions as $edition)
                        @if($edition->is_active)
                            <div class="p-4 rounded-lg border border-border bg-surface flex-1">
                                <div class="font-medium text-text-primary mb-1">{{ str($edition->format)->replace('_', ' ')->title() }}</div>
                                @if($edition->price > 0)
                                    <div class="text-primary font-semibold mb-2">Rp {{ number_format($edition->price, 0, ',', '.') }}</div>
                                @else
                                    <div class="text-green-600 font-semibold mb-2">Gratis</div>
                                @endif
                                @if($edition->stock !== null)
                                    <div class="text-sm text-text-muted">Stok: {{ $edition->stock }}</div>
                                @endif
                            </div>
                        @endif
                    @endforeach
                </div>
            </div>
        @endif

        <!-- Synopsis -->
        <div>
            <h2 class="text-2xl font-bold text-text-primary mb-6">Tentang Buku Ini</h2>
            @if($item->synopsis || $item->description)
                <div class="prose dark:prose-invert max-w-none text-text-secondary leading-relaxed space-y-4">
                    {!! nl2br(e($item->synopsis ?: $item->description)) !!}
                </div>
            @else
                <x-empty-state icon="document-text" title="Tidak ada deskripsi" description="Informasi tambahan untuk buku ini belum tersedia." class="border-0 shadow-none bg-surface" />
            @endif
        </div>
    </div>
</div>
