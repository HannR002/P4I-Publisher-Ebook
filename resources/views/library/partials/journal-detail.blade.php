<div class="mb-12">
    <div class="flex flex-wrap gap-2 mb-4">
        <x-badge color="primary">Jurnal Akademik</x-badge>
        @foreach($item->categories as $category)
            <x-badge color="gray">{{ $category->name }}</x-badge>
        @endforeach
    </div>

    <h1 class="text-3xl sm:text-4xl md:text-5xl font-bold text-text-primary mb-6 leading-tight">
        {{ $item->title }}
    </h1>

    @if($item->source_url)
        <div class="mb-8">
            <x-button tag="a" :href="$item->source_url" target="_blank" color="primary" class="!px-6 !py-2.5">
                Buka Sumber Eksternal / OJS
            </x-button>
        </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-8">
        <!-- Main Info -->
        <div class="lg:col-span-2">
            <h2 class="text-2xl font-bold text-text-primary mb-4 border-b border-border pb-2">Tentang Jurnal</h2>
            @if($item->description || $item->abstract)
                <div class="prose dark:prose-invert max-w-none text-text-secondary leading-relaxed">
                    {!! nl2br(e($item->description ?: $item->abstract)) !!}
                </div>
            @else
                <x-empty-state icon="document-text" title="Tidak ada deskripsi" description="Informasi tentang ruang lingkup jurnal ini belum tersedia." class="border-0 shadow-none bg-transparent px-0" />
            @endif
        </div>

        <!-- Metadata Sidebar -->
        <div class="lg:col-span-1">
            <div class="bg-surface rounded-xl p-6 border border-border shadow-sm">
                <h3 class="text-lg font-bold text-text-primary mb-4">Informasi Jurnal</h3>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Penerbit</dt>
                        <dd class="font-medium text-text-primary">{{ $item->publisher ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-muted mb-1">ISSN</dt>
                        <dd class="font-medium text-text-primary">{{ $item->issn ?: '—' }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-text-muted mb-1">Bahasa</dt>
                        <dd class="font-medium text-text-primary">{{ $item->language ? str($item->language)->upper() : '—' }}</dd>
                    </div>
                </dl>
            </div>
        </div>
    </div>
</div>

<!-- Issues List -->
<div class="mt-12 pt-12 border-t border-border">
    <h2 class="text-2xl font-bold text-text-primary mb-6">Edisi / Issues</h2>

    @if($item->children->count() > 0)
        <div class="grid grid-cols-1 sm:grid-cols-2 md:grid-cols-3 lg:grid-cols-4 gap-4">
            @foreach($item->children->sortByDesc('publication_date')->sortByDesc('volume')->sortByDesc('issue') as $issue)
                <a href="{{ route('library.show', $issue) }}" class="block group">
                    <x-card class="h-full hover:border-primary hover:shadow-md transition-all duration-200">
                        <div class="p-6 text-center h-full flex flex-col justify-center">
                            <div class="text-text-muted text-sm font-medium mb-1">{{ $issue->publication_year ?: ($issue->publication_date ? $issue->publication_date->format('Y') : '') }}</div>
                            <h3 class="text-lg font-bold text-text-primary group-hover:text-primary transition-colors">
                                Vol. {{ $issue->volume ?: '-' }} No. {{ $issue->issue ?: '-' }}
                            </h3>
                            @if($issue->title)
                                <div class="text-sm text-text-secondary mt-2">{{ Str::limit($issue->title, 40) }}</div>
                            @endif
                        </div>
                    </x-card>
                </a>
            @endforeach
        </div>
    @else
        <x-empty-state icon="collection" title="Belum ada edisi" description="Jurnal ini belum memiliki edisi atau terbitan yang dipublikasikan." />
    @endif
</div>
