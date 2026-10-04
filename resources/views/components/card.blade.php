<div {{ $attributes->merge(['class' => 'bg-surface border border-border rounded-xl shadow-sm overflow-hidden']) }}>
    @isset($header)
        <div class="px-6 py-4 border-b border-border bg-surface-muted/50">
            {{ $header }}
        </div>
    @endisset

    <div class="p-6">
        {{ $slot }}
    </div>

    @isset($footer)
        <div class="px-6 py-4 border-t border-border bg-surface-muted/50">
            {{ $footer }}
        </div>
    @endisset
</div>
