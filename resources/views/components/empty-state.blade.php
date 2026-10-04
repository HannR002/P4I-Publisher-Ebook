@props([
    'title',
    'description' => null,
    'icon' => null,
])

<div {{ $attributes->merge(['class' => 'flex flex-col items-center justify-center p-12 text-center bg-surface border border-border border-dashed rounded-xl']) }}>
    @if($icon)
        <div class="mx-auto flex h-12 w-12 items-center justify-center rounded-full bg-surface-muted text-text-muted mb-4">
            {{ $icon }}
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
