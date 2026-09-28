@props(['disabled' => false])

<input {{ $disabled ? 'disabled' : '' }} {!! $attributes->merge(['class' => 'border-[#2d3147] bg-[#12141f] text-gray-300 focus:border-indigo-500 focus:ring-indigo-500 rounded-xl shadow-inner px-4 py-3 w-full transition-colors']) !!}>
