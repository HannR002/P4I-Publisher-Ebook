<x-author-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center">
            <h2 class="font-semibold text-xl text-text-primary leading-tight">
                {{ __('Daftar Naskah Saya') }}
            </h2>
            <div class="mt-4 sm:mt-0">
                <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover transition-colors">
                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                    </svg>
                    Ajukan Naskah Baru
                </a>
            </div>
        </div>
    </x-slot>

    <div class="bg-surface shadow-sm rounded-xl border border-border overflow-hidden">
        <div class="overflow-x-auto">
            <table class="min-w-full divide-y divide-border">
                <thead class="bg-surface-hover">
                    <tr>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Info Naskah</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Kategori</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Status</th>
                        <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Tgl Kirim</th>
                        <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">Aksi</th>
                    </tr>
                </thead>
                <tbody class="bg-surface divide-y divide-border">
                    @forelse($submissions as $submission)
                    <tr class="hover:bg-surface-hover transition-colors">
                        <td class="px-6 py-4">
                            <div class="flex items-center">
                                @if($submission->cover_preview_path)
                                    <div class="flex-shrink-0 h-14 w-10 overflow-hidden rounded-md border border-border">
                                        <img class="h-full w-full object-cover" src="{{ asset('storage/' . $submission->cover_preview_path) }}" alt="{{ $submission->title }}">
                                    </div>
                                @else
                                    <div class="flex-shrink-0 h-14 w-10 bg-background rounded-md border border-border flex items-center justify-center">
                                        <svg class="h-5 w-5 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z" />
                                        </svg>
                                    </div>
                                @endif
                                <div class="ml-4">
                                    <div class="text-sm font-medium text-text-primary">{{ $submission->title }}</div>
                                    <div class="text-xs text-text-secondary truncate w-48">{{ Str::limit($submission->synopsis, 50) }}</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <span class="inline-flex items-center px-2.5 py-0.5 rounded-full text-xs font-medium bg-background text-text-secondary border border-border">
                                {{ $submission->category->name ?? '-' }}
                            </span>
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap">
                            <x-submission-status-badge :status="$submission->status" />
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                            {{ $submission->created_at->format('d M Y') }}
                        </td>
                        <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium space-x-3">
                            @if(in_array($submission->status, ['draft', 'revision_requested']))
                                <a href="{{ route('author.submissions.edit', $submission->id) }}" class="text-primary hover:text-primary-hover">Edit</a>
                            @endif
                            <a href="{{ route('author.submissions.show', $submission->id) }}" class="text-text-secondary hover:text-text-primary">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-6 py-12 text-center">
                            <svg class="mx-auto h-12 w-12 text-text-muted" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z" />
                            </svg>
                            <h3 class="mt-2 text-sm font-medium text-text-primary">Belum ada naskah yang diajukan.</h3>
                            <div class="mt-6">
                                <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover">
                                    <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                                    </svg>
                                    Ajukan Naskah Pertama
                                </a>
                            </div>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        <div class="bg-surface px-4 py-3 border-t border-border sm:px-6">
            {{ $submissions->links() }}
        </div>
    </div>
</x-author-layout>
