<div class="mb-10 text-center max-w-3xl mx-auto">
    <div class="text-text-muted font-medium mb-2">{{ $item->parent ? $item->parent->title : 'Jurnal Akademik' }}</div>
    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-text-primary mb-4 leading-tight">
        @if($item->title)
            {{ $item->title }}
        @else
            Vol. {{ $item->volume ?: '-' }} No. {{ $item->issue ?: '-' }}
            @if($item->publication_year || $item->publication_date)
                ({{ $item->publication_year ?: $item->publication_date->format('Y') }})
            @endif
        @endif
    </h1>
    @if($item->title)
        <div class="text-xl text-text-secondary font-medium">
            Vol. {{ $item->volume ?: '-' }} No. {{ $item->issue ?: '-' }}
            @if($item->publication_year || $item->publication_date)
                ({{ $item->publication_year ?: $item->publication_date->format('Y') }})
            @endif
        </div>
    @endif

    @if($item->source_url)
        <div class="mt-6">
            <x-button tag="a" :href="$item->source_url" target="_blank" color="secondary" class="!px-6 !py-2">
                Buka Edisi di OJS
            </x-button>
        </div>
    @endif
</div>

@if($item->description)
    <div class="mb-12 max-w-4xl mx-auto text-center prose dark:prose-invert text-text-secondary">
        {!! nl2br(e($item->description)) !!}
    </div>
@endif

<!-- Articles List -->
<div class="mt-10 pt-10 border-t border-border">
    <h2 class="text-2xl font-bold text-text-primary mb-6">Daftar Artikel</h2>

    @if($item->children->count() > 0)
        <div class="space-y-6">
            @foreach($item->children->sortBy('page_start') as $article)
                <x-card class="hover:border-primary transition-colors duration-200">
                    <div class="p-6">
                        <div class="flex flex-col sm:flex-row sm:items-start justify-between gap-4">
                            <div class="flex-1">
                                <h3 class="text-xl font-bold text-text-primary mb-2">
                                    <a href="{{ route('library.show', $article) }}" class="hover:text-primary hover:underline">
                                        {{ $article->title }}
                                    </a>
                                </h3>
                                @if($article->creators->isNotEmpty())
                                    <div class="text-text-secondary font-medium mb-3">
                                        {{ $article->creators->map(fn($c) => $c->name)->join(', ') }}
                                    </div>
                                @endif

                                <div class="flex flex-wrap gap-4 text-sm text-text-muted">
                                    @if($article->page_start)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21h10a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v14a2 2 0 002 2z" /></svg>
                                            Halaman {{ $article->page_start }} - {{ $article->page_end ?: '—' }}
                                        </div>
                                    @endif
                                    @if($article->doi)
                                        <div class="flex items-center gap-1">
                                            <svg class="w-4 h-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1" /></svg>
                                            {{ $article->doi }}
                                        </div>
                                    @endif
                                </div>
                            </div>

                            <div class="sm:text-right mt-4 sm:mt-0 flex flex-row sm:flex-col gap-2">
                                <x-button tag="a" :href="route('library.show', $article)" color="primary" class="!px-4 !py-1.5 text-sm whitespace-nowrap">
                                    Lihat Abstrak
                                </x-button>

                                @if($article->files->where('download_allowed', true)->isNotEmpty())
                                    <x-button tag="a" :href="route('library.download', $article)" color="secondary" class="!px-4 !py-1.5 text-sm whitespace-nowrap">
                                        PDF
                                    </x-button>
                                @elseif($article->access_policy === 'external' && $article->source_url)
                                    <x-button tag="a" :href="$article->source_url" target="_blank" color="secondary" class="!px-4 !py-1.5 text-sm whitespace-nowrap">
                                        OJS
                                    </x-button>
                                @endif
                            </div>
                        </div>
                    </div>
                </x-card>
            @endforeach
        </div>
    @else
        <x-empty-state icon="document-text" title="Belum ada artikel" description="Tidak ada artikel yang dipublikasikan pada edisi ini." />
    @endif
</div>
