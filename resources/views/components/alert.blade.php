@props([
    'variant' => 'info',
    'title' => null,
])

@php
    $baseClasses = 'rounded-lg p-4 border';

    $variants = [
        'success' => 'bg-success/10 border-success/30 text-success',
        'warning' => 'bg-warning/10 border-warning/30 text-warning',
        'danger' => 'bg-danger/10 border-danger/30 text-danger',
        'info' => 'bg-info/10 border-info/30 text-info',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['info']);
@endphp

<div {{ $attributes->merge(['class' => $classes]) }} role="alert">
    @if($title)
        <h3 class="text-sm font-bold mb-1">{{ $title }}</h3>
    @endif
    <div class="text-sm opacity-90">
        {{ $slot }}
    </div>
</div>
