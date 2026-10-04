@props([
    'title',
    'value',
    'icon' => null,
    'trend' => null,
    'trendUp' => true
])

<div {{ $attributes->merge(['class' => 'bg-surface border border-border rounded-xl p-6 shadow-sm flex items-start justify-between']) }}>
    <div>
        <p class="text-sm font-medium text-text-secondary truncate">
            {{ $title }}
        </p>
        <p class="mt-2 text-3xl font-bold text-text-primary">
            {{ $value }}
        </p>
        @if($trend)
            <p class="mt-1 text-sm {{ $trendUp ? 'text-success' : 'text-danger' }}">
                {{ $trend }}
            </p>
        @endif
    </div>
    @if($icon)
        <div class="p-3 rounded-full bg-primary-subtle text-primary">
            {{ $icon }}
        </div>
    @endif
</div>
