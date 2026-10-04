@props(['status'])

@php
    $config = match($status) {
        'draft' => ['color' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200', 'label' => 'Draf'],
        'submitted' => ['color' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/40 dark:text-blue-300', 'label' => 'Diajukan'],
        'in_review' => ['color' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/40 dark:text-yellow-300', 'label' => 'Sedang Direview'],
        'revision_requested' => ['color' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/40 dark:text-orange-300', 'label' => 'Perlu Revisi'],
        'approved' => ['color' => 'bg-green-100 text-green-800 dark:bg-green-900/40 dark:text-green-300', 'label' => 'Disetujui'],
        'published' => ['color' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/40 dark:text-emerald-300', 'label' => 'Terbit'],
        'rejected' => ['color' => 'bg-red-100 text-red-800 dark:bg-red-900/40 dark:text-red-300', 'label' => 'Ditolak'],
        default => ['color' => 'bg-gray-100 text-gray-800 dark:bg-gray-800 dark:text-gray-200', 'label' => ucfirst(str_replace('_', ' ', $status))],
    };
@endphp

<span {{ $attributes->merge(['class' => 'px-2.5 py-0.5 inline-flex text-xs font-semibold rounded-full ' . $config['color']]) }}>
    {{ $config['label'] }}
</span>
