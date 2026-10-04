@props([
    'id',
    'name',
    'label',
    'type' => 'text',
    'required' => false,
    'helpText' => null,
    'options' => [],
    'rows' => 3,
    'value' => null,
])

<div class="space-y-1">
    <label for="{{ $id }}" class="block text-sm font-medium text-text-primary">
        {{ $label }}
        @if($required)
            <span class="text-danger">*</span>
        @endif
    </label>

    @if($type === 'textarea')
        <textarea
            id="{{ $id }}"
            name="{{ $name }}"
            rows="{{ $rows }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-border bg-surface text-text-primary shadow-sm focus:border-primary focus:ring-primary sm:text-sm disabled:opacity-50']) }}
        >{{ old($name, $value) }}</textarea>
    @elseif($type === 'select')
        <select
            id="{{ $id }}"
            name="{{ $name }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-border bg-surface text-text-primary shadow-sm focus:border-primary focus:ring-primary sm:text-sm disabled:opacity-50']) }}
        >
            @foreach($options as $val => $text)
                <option value="{{ $val }}" @selected(old($name, $value) == $val)>{{ $text }}</option>
            @endforeach
        </select>
    @elseif($type === 'checkbox')
        <div class="flex items-center">
            <input
                id="{{ $id }}"
                name="{{ $name }}"
                type="checkbox"
                value="1"
                @checked(old($name, $value))
                {{ $required ? 'required' : '' }}
                {{ $attributes->merge(['class' => 'h-4 w-4 rounded border-border bg-surface text-primary focus:ring-primary disabled:opacity-50']) }}
            >
            @if($helpText)
                <span class="ml-2 block text-sm text-text-secondary">{{ $helpText }}</span>
            @endif
        </div>
    @else
        <input
            id="{{ $id }}"
            name="{{ $name }}"
            type="{{ $type }}"
            value="{{ old($name, $value) }}"
            {{ $required ? 'required' : '' }}
            {{ $attributes->merge(['class' => 'block w-full rounded-md border-border bg-surface text-text-primary shadow-sm focus:border-primary focus:ring-primary sm:text-sm disabled:opacity-50']) }}
        >
    @endif

    @error($name)
        <p class="text-sm text-danger mt-1">{{ $message }}</p>
    @enderror

    @if($helpText && $type !== 'checkbox')
        <p class="text-xs text-text-muted mt-1">{{ $helpText }}</p>
    @endif
</div>
