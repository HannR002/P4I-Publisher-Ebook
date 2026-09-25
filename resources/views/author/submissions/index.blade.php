<x-author-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Daftar Naskah Saya') }}
            </h2>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Naskah
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                <thead class="bg-gray-50 dark:bg-gray-900/50">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Info Naskah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Usulan Harga</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tgl Kirim</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-white dark:bg-[#1a1d24] divide-y divide-gray-200 dark:divide-gray-800">
                    @forelse($submissions as $submission)
                    <tr class="hover:bg-gray-50 dark:hover:bg-gray-800/50 transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($submission->cover_preview_path)
                                    <div class="flex-shrink-0 h-14 w-10 overflow-hidden rounded-md border border-gray-200 dark:border-gray-700">
                                        <img class="h-full w-full object-cover" src="{{ asset('storage/' . $submission->cover_preview_path) }}" alt="{{ $submission->title }}">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 h-14 w-10 bg-gray-200 dark:bg-gray-700 rounded-md border border-gray-300 dark:border-gray-600 flex items-center justify-center">
                                        <svg class="h-5 w-5 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $submission->title }}</div>
                                    <div class="text-xs text-gray-500 dark:text-gray-400 truncate w-32">{{ Str::limit($submission->synopsis, 40) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-gray-100 text-gray-800 dark:bg-gray-700 dark:text-gray-300">
                                {{ $submission->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900 dark:text-white font-medium">
                            Rp {{ number_format($submission->proposed_price, 0, ',', '.') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            @php
                                $colors = [
                                    'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300 border-gray-200 dark:border-gray-700',
                                    'submitted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300 border-blue-200 dark:border-blue-800',
                                    'in_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300 border-yellow-200 dark:border-yellow-800',
                                    'revision_requested' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300 border-orange-200 dark:border-orange-800',
                                    'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300 border-green-200 dark:border-green-800',
                                    'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300 border-emerald-200 dark:border-emerald-800',
                                    'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300 border-red-200 dark:border-red-800',
                                ];
                                $labels = [
                                    'draft' => 'Draf',
                                    'submitted' => 'Terkirim',
                                    'in_review' => 'Kurasi',
                                    'revision_requested' => 'Revisi',
                                    'approved' => 'Disetujui',
                                    'published' => 'Terbit',
                                    'rejected' => 'Ditolak',
                                ];
                            @endphp
                            <span class="px-2.5 py-1 inline-flex text-xs leading-5 font-semibold rounded-full border {{ $colors[$submission->status] ?? 'bg-gray-100 text-gray-800' }}">
                                {{ $labels[$submission->status] ?? $submission->status }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                            {{ $submission->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                            @if(in_array($submission->status, ['draft', 'revision_requested']))
                                <a href="{{ route('author.submissions.edit', $submission->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Edit</a>
                            @endif
                            <a href="{{ route('author.submissions.show', $submission->id) }}" class="text-gray-600 dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-200">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-gray-900 dark:text-white">Belum ada naskah</h3>
                            <p class="mt-1 text-sm text-gray-500 dark:text-gray-400">Mulailah dengan membuat pengajuan naskah baru.</p>
                            <div class="mt-6">
                                <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajukan Naskah Baru
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-white dark:bg-[#1a1d24] px-4 py-3 border-t border-gray-200 dark:border-gray-800 sm:px-6">
            {{ $submissions->links() }}
        </div>
    </div>
</x-author-layout>
