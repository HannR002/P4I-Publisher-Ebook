<x-public-layout>
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <!-- Hero Section -->
        <div class="mb-12 text-center max-w-3xl mx-auto">
            <h1 class="text-5xl lg:text-6xl font-black text-gray-900 dark:text-white tracking-tight mb-6 leading-tight">
                Koleksi Buku <br><span class="text-transparent bg-clip-text bg-gradient-to-r from-blue-600 to-emerald-600 dark:from-indigo-400 dark:to-emerald-400">Terbaik & Premium</span>
            </h1>
            <p class="text-lg text-gray-600 dark:text-gray-400 font-medium">Temukan literatur berkualitas yang telah dikurasi. Beli sekali, akses selamanya dengan keamanan DRM mutakhir.</p>
        </div>

        <!-- Search Bar -->
        <div class="max-w-2xl mx-auto mb-16">
            <form action="{{ route('books.index') }}" method="GET" class="relative">
                <div class="absolute inset-y-0 left-0 flex items-center pl-4 pointer-events-none">
                    <svg class="w-5 h-5 text-gray-400 dark:text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
                </div>
                <input type="text" name="search" value="{{ request('search') }}" class="block w-full p-4 pl-12 text-sm text-gray-900 bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-full focus:ring-blue-500 focus:border-blue-500 dark:text-white dark:placeholder-gray-400 dark:focus:ring-indigo-500 dark:focus:border-indigo-500 shadow-sm transition-colors" placeholder="Cari judul buku, penulis, atau genre...">
                <button type="submit" class="text-white absolute right-2 top-1/2 -translate-y-1/2 bg-black dark:bg-indigo-600 hover:bg-gray-800 dark:hover:bg-indigo-700 focus:ring-4 focus:outline-none focus:ring-blue-300 font-medium rounded-full text-sm px-6 py-2 transition-colors">
                    Cari
                </button>
            </form>
        </div>

        <!-- Catalog Grid (1-4 columns) -->
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-x-8 gap-y-12">
            @forelse ($books as $book)
                <div class="group flex flex-col h-full bg-white dark:bg-[#161615] rounded-3xl border border-gray-100 dark:border-[#2d3147] shadow-[0_8px_30px_rgb(0,0,0,0.04)] hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] hover:-translate-y-2 transition-all duration-500 ease-out relative overflow-visible z-10 hover:z-20">
                    
                    <!-- Cover Image Container (Elevated on hover) -->
                    <div class="w-full relative px-6 pt-6 mb-2">
                        <div class="aspect-[2/3] w-full rounded-xl overflow-hidden bg-gray-50 dark:bg-[#1a1d2e] shadow-md group-hover:shadow-2xl transition-shadow duration-500 relative">
                            @if($book->cover_image_path)
                                <img src="{{ $book->cover_image_path }}" alt="Cover {{ $book->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-700 ease-out">
                            @else
                                <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-600 bg-gray-100 dark:bg-[#1a1d2e]">
                                    <svg class="w-12 h-12 mb-2 opacity-50" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                </div>
                            @endif
                            
                            <!-- Badge "Sudah Dimiliki" -->
                            @if(in_array($book->id, $ownedBookIds))
                                <div class="absolute top-2 right-2 bg-emerald-500/95 backdrop-blur-sm text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-sm border border-emerald-400/50 uppercase tracking-widest">
                                    Dimiliki
                                </div>
                            @endif
                        </div>
                    </div>
                    
                    <!-- Book Info -->
                    <div class="px-6 pb-6 pt-2 flex flex-col flex-grow">
                        <div class="mb-4">
                            @if($book->author)
                                <p class="text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">{{ $book->author }}</p>
                            @endif
                            <h3 class="text-lg font-bold text-gray-900 dark:text-white leading-snug group-hover:text-blue-600 dark:group-hover:text-indigo-400 transition-colors">
                                <a href="{{ route('books.show', $book->slug) }}" class="focus:outline-none">
                                    <span class="absolute inset-0" aria-hidden="true"></span>
                                    {{ $book->title }}
                                </a>
                            </h3>
                            
                            @if($book->categories->count() > 0)
                                <div class="flex flex-wrap gap-1 mt-2">
                                    @foreach($book->categories->take(2) as $category)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-blue-50 dark:bg-indigo-900/30 text-blue-700 dark:text-indigo-300 border border-blue-100 dark:border-indigo-800 relative z-10">
                                            {{ $category->name }}
                                        </span>
                                    @endforeach
                                    @if($book->categories->count() > 2)
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-[10px] font-semibold bg-gray-50 dark:bg-gray-800 text-gray-500 dark:text-gray-400 border border-gray-100 dark:border-gray-700 relative z-10">
                                            +{{ $book->categories->count() - 2 }}
                                        </span>
                                    @endif
                                </div>
                            @endif
                        </div>
                        
                        <!-- Price -->
                        <div class="mt-auto pt-2">
                            <span class="text-xl font-black text-gray-900 dark:text-white">
                                Rp {{ number_format($book->price, 0, ',', '.') }}
                            </span>
                        </div>
                        
                        <!-- Action Button (Relative to stay above absolute link) -->
                        <div class="mt-5 relative z-10">
                            @if(in_array($book->id, $ownedBookIds))
                                <a href="{{ route('drm.reader', $book->id) }}" data-testid="btn-read-book-{{ $book->id }}" class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 hover:text-emerald-800 transition-colors border border-emerald-200 dark:border-emerald-800">
                                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                    Baca Sekarang
                                </a>
                            @else
                                <a href="{{ route('books.show', $book->slug) }}" data-testid="btn-checkout-{{ $book->id }}" class="w-full flex justify-center items-center py-2.5 px-4 rounded-xl text-sm font-bold text-white dark:text-gray-900 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors shadow-sm">
                                    Lihat Detail
                                </a>
                            @endif
                        </div>
                    </div>
                </div>
            @empty
                <div class="col-span-full py-20 text-center text-gray-500 dark:text-gray-400 bg-white dark:bg-[#161615] rounded-3xl border border-gray-100 dark:border-[#2d3147] shadow-sm">
                    <svg class="mx-auto w-16 h-16 mb-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <p class="text-xl font-medium text-gray-600 dark:text-gray-300">Buku tidak ditemukan.</p>
                </div>
            @endforelse
        </div>
        
        <!-- Pagination -->
        @if($books->hasPages())
            <div class="mt-16 pt-8 border-t border-gray-200 dark:border-[#2d3147]">
                {{ $books->links() }}
            </div>
        @endif
    </div>
</x-public-layout>
