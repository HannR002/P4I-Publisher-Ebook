@props(['value'])

<label {{ $attributes->merge(['class' => 'block font-semibold text-sm text-gray-300']) }}>
    {{ $value ?? $slot }}
</label>
