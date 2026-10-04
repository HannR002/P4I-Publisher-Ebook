<div class="grid grid-cols-1 lg:grid-cols-12 gap-8">
    <div class="lg:col-span-8 lg:pr-8">
        <!-- Title & Creators -->
        <div class="mb-8 border-b border-border pb-8">
            <h1 class="text-3xl sm:text-4xl font-bold text-text-primary mb-4 leading-tight">
                {{ $item->title }}
            </h1>

            <div class="text-xl text-text-secondary font-medium mb-6">
                @if($item->creators->isNotEmpty())
                    {{ $item->creators->map(fn($c) => $c->name)->join(', ') }}
                @else
                    Penulis Tidak Diketahui
                @endif
            </div>

            <div class="flex flex-wrap gap-4 mt-6">
                @if($actions['can_read'])
                    <x-button tag="a" :href="route('library.read', $item)" color="primary" class="!px-6 !py-3">
                        {{ $actions['is_external'] ? 'Baca di OJS / Sumber' : 'Baca Artikel' }}
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
            </div>
        </div>

        <!-- Abstract -->
        <div class="mb-10">
            <h2 class="text-2xl font-bold text-text-primary mb-4">Abstrak / Ringkasan Penelitian</h2>
            @if($item->abstract || $item->description)
                <div class="prose dark:prose-invert max-w-none text-text-secondary leading-relaxed space-y-4">
                    {!! nl2br(e($item->abstract ?: $item->description)) !!}
                </div>
            @else
                <x-empty-state icon="document-text" title="Tidak ada abstrak" description="Abstrak untuk artikel ini belum tersedia." class="border-0 shadow-none bg-surface px-0" />
            @endif
        </div>

        <!-- Featured Excerpt -->
        @if($item->featured_excerpt)
            <div class="mb-10 bg-surface rounded-xl p-6 border-l-4 border-primary">
                <h3 class="text-lg font-bold text-text-primary mb-3 flex items-center gap-2">
                    <svg class="w-5 h-5 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M14.017 21v-7.391c0-5.704 3.731-9.57 8.983-10.609l.995 2.151c-2.432.917-3.995 3.638-3.995 5.849h4v10h-9.983zm-14.017 0v-7.391c0-5.704 3.748-9.57 9-10.609l.996 2.151c-2.433.917-3.996 3.638-3.996 5.849h3.983v10h-9.983z"/></svg>
                    Cuplikan
                </h3>
                <blockquote class="italic text-text-secondary text-lg mb-4">
                    "{{ $item->featured_excerpt }}"
                </blockquote>
                <div class="text-sm font-medium text-text-muted">
                    &mdash; {{ $item->excerpt_source ?: 'Sumber' }}
                    @if($item->excerpt_page)
                        <span class="text-text-muted/60">| Halaman {{ $item->excerpt_page }}</span>
                    @endif
                </div>
            </div>
        @endif
    </div>

    <!-- Metadata Sidebar -->
    <div class="lg:col-span-4">
        <div class="bg-surface rounded-xl p-6 border border-border shadow-sm sticky top-6">
            <h3 class="text-lg font-bold text-text-primary mb-4 border-b border-border pb-3">Informasi Artikel</h3>
            <dl class="space-y-4">
                @if($item->parent && $item->parent->parent)
                    <!-- Is in an Issue -> Journal -->
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Jurnal</dt>
                        <dd class="font-medium text-text-primary">
                            <a href="{{ route('library.show', $item->parent->parent) }}" class="text-primary hover:underline">
                                {{ $item->parent->parent->title }}
                            </a>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Edisi / Issue</dt>
                        <dd class="font-medium text-text-primary">
                            <a href="{{ route('library.show', $item->parent) }}" class="text-primary hover:underline">
                                Vol. {{ $item->parent->volume ?: '-' }} No. {{ $item->parent->issue ?: '-' }}
                            </a>
                        </dd>
                    </div>
                @elseif($item->parent)
                    <!-- Direct child of Journal -->
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Jurnal</dt>
                        <dd class="font-medium text-text-primary">
                            <a href="{{ route('library.show', $item->parent) }}" class="text-primary hover:underline">
                                {{ $item->parent->title }}
                            </a>
                        </dd>
                    </div>
                @endif

                @if($item->publication_year || $item->publication_date)
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Tahun Publikasi</dt>
                        <dd class="font-medium text-text-primary">{{ $item->publication_year ?: $item->publication_date->format('Y') }}</dd>
                    </div>
                @endif

                @if($item->page_start)
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Halaman</dt>
                        <dd class="font-medium text-text-primary">{{ $item->page_start }} - {{ $item->page_end ?: '—' }}</dd>
                    </div>
                @endif

                @if($item->doi)
                    <div>
                        <dt class="text-sm text-text-muted mb-1">DOI</dt>
                        <dd class="font-medium text-text-primary">
                            <a href="https://doi.org/{{ $item->doi }}" target="_blank" class="text-primary hover:underline break-all">{{ $item->doi }}</a>
                        </dd>
                    </div>
                @endif

                @if($actions['is_external'] && $item->source_url)
                    <div class="mt-6 pt-6 border-t border-border">
                        <div class="flex items-center gap-2 text-text-secondary font-medium">
                            <svg class="w-5 h-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14" /></svg>
                            Sumber: OJS / Eksternal
                        </div>
                    </div>
                @endif
            </dl>

            <div class="mt-6 flex flex-wrap gap-2">
                @foreach($item->categories as $category)
                    <x-badge color="gray">{{ $category->name }}</x-badge>
                @endforeach
            </div>
        </div>
    </div>
</div>
