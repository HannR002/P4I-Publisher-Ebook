@props([
    'variant' => 'neutral'
])

@php
    $baseClasses = 'inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-semibold';

    $variants = [
        'neutral' => 'bg-surface-elevated text-text-secondary border border-border',
        'primary' => 'bg-primary-subtle text-primary border border-primary/20',
        'success' => 'bg-success/10 text-success border border-success/20',
        'warning' => 'bg-warning/10 text-warning border border-warning/20',
        'danger' => 'bg-danger/10 text-danger border border-danger/20',
        'info' => 'bg-info/10 text-info border border-info/20',
    ];

    $classes = $baseClasses . ' ' . ($variants[$variant] ?? $variants['neutral']);
@endphp

<span {{ $attributes->merge(['class' => $classes]) }}>
    {{ $slot }}
</span>
