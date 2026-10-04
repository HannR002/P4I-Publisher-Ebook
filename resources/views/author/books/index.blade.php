<x-author-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-text-primary leading-tight">
            {{ __('Buku Saya') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            <div class="bg-surface shadow sm:rounded-lg border border-border p-6">
                @if($books->isEmpty())
                    <div class="text-center py-12">
                        <svg class="mx-auto h-12 w-12 text-text-secondary" fill="none" viewBox="0 0 24 24" stroke="currentColor" aria-hidden="true">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                        </svg>
                        <h3 class="mt-2 text-sm font-semibold text-text-primary">Tidak ada buku yang diterbitkan</h3>
                        <p class="mt-1 text-sm text-text-secondary">Anda belum memiliki buku yang diterbitkan oleh P4I.</p>
                        <div class="mt-6">
                            <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 bg-primary border border-transparent rounded-md font-semibold text-xs text-white uppercase tracking-widest hover:bg-primary-hover focus:bg-primary-hover active:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150">
                                Ajukan Penerbitan Baru
                            </a>
                        </div>
                    </div>
                @else
                    <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-6">
                        @foreach($books as $book)
                            <div class="bg-background rounded-2xl border border-border overflow-hidden flex flex-col hover:border-primary/50 transition-colors">
                                @if($book->cover_image_path)
                                    <div class="aspect-[3/4] w-full relative">
                                        <img src="{{ Storage::url($book->cover_image_path) }}" alt="{{ $book->title }}" class="absolute inset-0 w-full h-full object-cover">
                                    </div>
                                @else
                                    <div class="aspect-[3/4] w-full bg-surface-hover flex items-center justify-center">
                                        <svg class="w-12 h-12 text-text-secondary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    </div>
                                @endif
                                
                                <div class="p-4 flex flex-col flex-1">
                                    <h3 class="font-bold text-text-primary text-lg leading-tight mb-2 line-clamp-2" title="{{ $book->title }}">{{ $book->title }}</h3>
                                    <div class="flex items-center gap-2 mb-4 mt-auto">
                                        <span class="inline-flex items-center rounded-md bg-green-50 px-2 py-1 text-xs font-medium text-green-700 ring-1 ring-inset ring-green-600/20 dark:bg-green-500/10 dark:text-green-400 dark:ring-green-500/20">Terbit</span>
                                        <span class="text-xs text-text-secondary">{{ $book->publish_date ? $book->publish_date->format('Y') : '-' }}</span>
                                    </div>
                                    
                                    @if($book->libraryItem)
                                        <a href="{{ route('library.show', $book->libraryItem) }}" class="w-full inline-flex justify-center items-center px-4 py-2 bg-surface border border-border rounded-xl font-semibold text-xs text-text-primary uppercase tracking-widest hover:bg-surface-hover hover:border-text-secondary focus:outline-none focus:ring-2 focus:ring-primary focus:ring-offset-2 transition ease-in-out duration-150 gap-2">
                                            Lihat di Perpustakaan
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                                        </a>
                                    @else
                                        <button disabled class="w-full inline-flex justify-center items-center px-4 py-2 bg-background border border-border rounded-xl font-semibold text-xs text-text-secondary uppercase tracking-widest cursor-not-allowed">
                                            Belum Masuk Katalog
                                        </button>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                    
                    <div class="mt-6">
                        {{ $books->links() }}
                    </div>
                @endif
            </div>
        </div>
    </div>
</x-author-layout>
