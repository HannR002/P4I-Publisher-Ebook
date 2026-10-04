<div {{ $attributes->merge(['class' => 'bg-surface border border-border rounded-xl shadow-sm overflow-x-auto']) }}>
    <table class="w-full text-sm text-left text-text-primary whitespace-nowrap">
        {{ $slot }}
    </table>
</div>
