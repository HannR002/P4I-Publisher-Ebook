@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-12 text-center bg-surface border border-border border-dashed rounded-xl']) }}>
    @if($icon)
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-surface-muted text-text-muted mb-4">
            @if($icon === 'document-text')
                <svg fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-6 h-6"><path stroke-linecap="round" stroke-linejoin="round" d="M19.5 14.25v-2.625a3.375 3.375 0 0 0-3.375-3.375h-1.5A1.125 1.125 0 0 1 13.5 7.125v-1.5a3.375 3.375 0 0 0-3.375-3.375H8.25m2.25 0H5.625c-.621 0-1.125.504-1.125 1.125v17.25c0 .621.504 1.125 1.125 1.125h12.75c.621 0 1.125-.504 1.125-1.125V11.25a9 9 0 0 0-9-9Z" /></svg>
            @else
                {!! $icon !!}
            @endif
        </div>
    @endif
    <h3 class="text-lg font-bold text-text-primary">{{ $title }}</h3>
    @if($description)
        <p class="mt-2 text-sm text-text-secondary max-w-sm">{{ $description }}</p>
    @endif
    @if(isset($action))
        <div class="mt-6">
            {{ $action }}
        </div>
    @endif
</div>
