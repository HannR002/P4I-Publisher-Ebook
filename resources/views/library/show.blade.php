<x-public-layout>
    <!-- Breadcrumbs -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-4">
        @php
            $links = ['Perpustakaan' => route('library.index')];
            if ($item->parent) {
                if ($item->parent->type === 'journal') {
                    $links[$item->parent->title] = route('library.show', $item->parent);
                } elseif ($item->parent->parent) {
                    $links[$item->parent->parent->title] = route('library.show', $item->parent->parent);
                    $links['Edisi ' . $item->parent->title] = route('library.show', $item->parent);
                }
            }
            $links[Str::limit($item->title, 40)] = null;
        @endphp
        <x-breadcrumb :links="$links" />
    </div>

    <!-- Main Content -->
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 pb-16">
        @if($item->type === 'book' || $item->type === 'module' || $item->type === 'monograph')
            @include('library.partials.book-detail')
        @elseif($item->type === 'journal')
            @include('library.partials.journal-detail')
        @elseif($item->type === 'journal_issue')
            @include('library.partials.issue-detail')
        @elseif(in_array($item->type, ['journal_article', 'article', 'proceeding']))
            @include('library.partials.article-detail')
        @else
            @include('library.partials.generic-detail')
        @endif
    </div>
</x-public-layout>
