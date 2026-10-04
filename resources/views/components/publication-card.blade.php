@props([
    'item',
    'url' => '#',
])

@php
    $coverUrl = $item->cover_image_path ? Storage::disk('public')->url($item->cover_image_path) : null;
    $typeLabel = \App\Models\LibraryItem::getLocalizedType($item->type);
@endphp

<a href="{{ $url }}" class="group block bg-surface border border-border rounded-xl shadow-sm hover:shadow-md hover:border-border-strong transition-all overflow-hidden flex flex-col h-full focus:outline-none focus:ring-2 focus:ring-focus-ring focus:ring-offset-2">
    <!-- Cover Area -->
    <div class="relative w-full aspect-[2/3] bg-surface-muted border-b border-border flex items-center justify-center overflow-hidden p-0">
        @if($coverUrl)
            <!-- Cover image with shadow and border to ensure it pops in both themes -->
            <img src="{{ $coverUrl }}" alt="Cover for {{ $item->title }}" class="w-full h-full object-cover transition-transform duration-500 group-hover:scale-105" loading="lazy">
            <div class="absolute inset-0 ring-1 ring-inset ring-black/10 dark:ring-white/10 pointer-events-none"></div>
        @else
            <!-- Placeholder -->
            <div class="flex flex-col items-center justify-center p-4 text-text-muted">
                <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
            </div>
        @endif

        <!-- Badge -->
        <div class="absolute top-2 right-2">
            <x-badge variant="neutral" class="bg-surface/90 backdrop-blur-sm border-transparent">{{ $typeLabel }}</x-badge>
        </div>
    </div>

    <!-- Metadata Area -->
    <div class="p-4 flex flex-col flex-grow">
        <h3 class="text-base font-bold text-text-primary leading-snug line-clamp-2 group-hover:text-primary transition-colors">
            {{ $item->title }}
        </h3>

        <p class="mt-1 text-sm text-text-secondary line-clamp-1">
            {{ $item->creators->pluck('name')->join(', ') ?: 'Unknown Author' }}
        </p>

        <div class="mt-auto pt-4 flex items-center justify-between">
            <span class="text-xs font-medium text-text-muted uppercase tracking-wider">
                {{ \App\Models\LibraryItem::getLocalizedAccessPolicy($item->access_policy) }}
            </span>
        </div>
    </div>
</a>
