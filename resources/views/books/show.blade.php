<x-public-layout>
    <div class="max-w-6xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        <!-- Back Link -->
        <div class="mb-8">
            <a href="{{ route('books.index') }}" class="inline-flex items-center text-sm font-semibold text-gray-500 dark:text-gray-400 hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Katalog
            </a>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-12 gap-10">
            <!-- Left Column: Cover Image -->
            <div class="md:col-span-4 lg:col-span-3">
                <div class="bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] rounded-3xl p-4 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative hover:shadow-[0_20px_40px_rgb(0,0,0,0.08)] transition-all duration-500">
                    <div class="aspect-[2/3] w-full rounded-2xl overflow-hidden bg-gray-50 dark:bg-[#1a1d2e] shadow-inner group">
                        @if($book->cover_image_path)
                            <img src="{{ $book->cover_image_path }}" alt="Cover {{ $book->title }}" class="w-full h-full object-cover">
                        @else
                            <div class="w-full h-full flex flex-col items-center justify-center text-gray-400 dark:text-gray-600">
                                <svg class="w-16 h-16 opacity-50 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span class="text-xs uppercase tracking-widest font-bold">No Cover</span>
                            </div>
                        @endif
                    </div>

                    @if($isOwned)
                        <div class="absolute top-2 right-2 bg-emerald-500/95 backdrop-blur-sm text-white text-[10px] font-bold px-3 py-1.5 rounded-full shadow-sm border border-emerald-400/50 uppercase tracking-widest">
                            Dimiliki
                        </div>
                    @endif
                </div>
            </div>

            <!-- Right Column: Details & Action -->
            <div class="md:col-span-8 lg:col-span-9 flex flex-col">
                <div class="bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] rounded-3xl p-8 lg:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] flex-grow relative overflow-hidden">
                    <!-- Subtle background decoration -->
                    <div class="absolute -right-24 -top-24 w-64 h-64 bg-blue-500/5 dark:bg-indigo-500/10 rounded-full blur-3xl pointer-events-none"></div>

                    <div class="relative z-10">
                        <h1 class="text-3xl lg:text-5xl font-extrabold text-gray-900 dark:text-white leading-tight mb-2 tracking-tight">
                            {{ $book->title }}
                        </h1>
                        <p class="text-lg text-blue-600 dark:text-indigo-400 font-medium mb-4">oleh {{ $book->author }}</p>

                        @if($book->categories->count() > 0)
                            <div class="flex flex-wrap gap-2 mb-6">
                                @foreach($book->categories as $category)
                                    <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-semibold bg-gray-100 dark:bg-gray-800 text-gray-800 dark:text-gray-200 border border-transparent dark:border-[#3E3E3A]">
                                        {{ $category->name }}
                                    </span>
                                @endforeach
                            </div>
                        @endif

                        <div class="prose prose-blue dark:prose-invert max-w-none text-gray-600 dark:text-gray-300 mb-6 leading-relaxed font-medium">
                            <p>{{ $book->description }}</p>
                        </div>

                        <!-- Book Metadata -->
                        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-10">
                            <div class="bg-gray-50 dark:bg-[#1a1d2e] p-4 rounded-xl border border-gray-100 dark:border-[#2d3147]">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">ISBN</span>
                                <span class="font-mono text-sm text-gray-900 dark:text-white">{{ $book->isbn ?? 'Tidak Tersedia' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-[#1a1d2e] p-4 rounded-xl border border-gray-100 dark:border-[#2d3147]">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Halaman</span>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $book->pages ? $book->pages . ' Halaman' : 'Tidak Tersedia' }}</span>
                            </div>
                            <div class="bg-gray-50 dark:bg-[#1a1d2e] p-4 rounded-xl border border-gray-100 dark:border-[#2d3147]">
                                <span class="block text-xs font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Tanggal Terbit</span>
                                <span class="font-bold text-sm text-gray-900 dark:text-white">{{ $book->publish_date ? \Carbon\Carbon::parse($book->publish_date)->translatedFormat('d M Y') : 'Tidak Tersedia' }}</span>
                            </div>
                        </div>

                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between border-t border-gray-100 dark:border-[#2d3147] pt-8 mt-auto">
                            <div class="mb-6 sm:mb-0">
                                <span class="block text-sm font-semibold text-gray-500 dark:text-gray-400 uppercase tracking-wider mb-1">Harga Resmi</span>
                                <span class="text-3xl lg:text-4xl font-black text-gray-900 dark:text-white">
                                    Rp {{ number_format($book->price, 0, ',', '.') }}
                                </span>
                            </div>

                            <div class="w-full sm:w-auto min-w-[250px]">
                                @if($isOwned)
                                    <a href="{{ route('drm.reader', $book->id) }}" class="w-full flex justify-center items-center py-4 px-8 rounded-xl text-base font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 hover:text-emerald-800 transition-colors border border-emerald-200 dark:border-emerald-800 shadow-sm">
                                        <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                        BACA SEKARANG
                                    </a>
                                @else
                                    @auth
                                        <!-- Form Checkout POST untuk User yang Login -->
                                        <form method="POST" action="{{ route('checkout.store') }}">
                                            @csrf
                                            <input type="hidden" name="book_ids[]" value="{{ $book->id }}">
                                            <button type="submit" class="w-full flex justify-center items-center py-4 px-8 rounded-xl shadow-sm text-base font-bold text-white dark:text-gray-900 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors transform hover:-translate-y-1">
                                                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                                BELI SEKARANG
                                            </button>
                                        </form>
                                    @else
                                        <!-- Redirect ke Login dengan menyimpan Intended URL -->
                                        <a href="{{ route('checkout.login') }}" class="w-full flex justify-center items-center py-4 px-8 rounded-xl shadow-sm text-base font-bold text-white dark:text-gray-900 bg-black dark:bg-white hover:bg-gray-800 dark:hover:bg-gray-200 transition-colors transform hover:-translate-y-1">
                                            <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 3h2l.4 2M7 13h10l4-8H5.4M7 13L5.4 5M7 13l-2.293 2.293c-.63.63-.184 1.707.707 1.707H17m0 0a2 2 0 100 4 2 2 0 000-4zm-8 2a2 2 0 11-4 0 2 2 0 014 0z"></path></svg>
                                            BELI SEKARANG
                                        </a>
                                        <p class="text-xs text-gray-500 dark:text-gray-400 mt-3 text-center">Anda akan diarahkan untuk login terlebih dahulu.</p>
                                    @endauth
                                @endif
                            </div>
                        </div>
                    </div>
                </div>

                <!-- Security Badges -->
                <div class="mt-6 grid grid-cols-1 sm:grid-cols-3 gap-4">
                    <div class="bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] shadow-sm rounded-xl p-4 flex items-center justify-center text-center gap-3 hover:shadow-md transition-shadow">
                        <svg class="w-8 h-8 text-blue-500 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>
                        <div class="text-left">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Enkripsi DRM</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Standar industri</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] shadow-sm rounded-xl p-4 flex items-center justify-center text-center gap-3 hover:shadow-md transition-shadow">
                        <svg class="w-8 h-8 text-emerald-500 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"></path></svg>
                        <div class="text-left">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Bayar Aman</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">Via Midtrans</p>
                        </div>
                    </div>
                    <div class="bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] shadow-sm rounded-xl p-4 flex items-center justify-center text-center gap-3 hover:shadow-md transition-shadow">
                        <svg class="w-8 h-8 text-purple-500 dark:text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                        <div class="text-left">
                            <h4 class="text-sm font-bold text-gray-900 dark:text-white">Akses Selamanya</h4>
                            <p class="text-xs text-gray-500 dark:text-gray-400">1x Bayar</p>
                        </div>
                    </div>
                </div>

                <!-- Review Section -->
                <div class="mt-8 bg-white dark:bg-[#161615] border border-gray-100 dark:border-[#2d3147] rounded-3xl p-8 lg:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                    <h3 class="text-2xl font-bold text-gray-900 dark:text-white mb-6">Ulasan & Rating</h3>

                    @if($isOwned)
                        <div class="mb-8 border-b border-gray-100 dark:border-[#2d3147] pb-8">
                            <h4 class="text-lg font-semibold dark:text-white mb-4">{{ $userReview ? 'Edit Ulasan Anda' : 'Berikan Ulasan Anda' }}</h4>
                            @if(session('success'))
                                <div class="bg-emerald-50 dark:bg-emerald-900/20 text-emerald-700 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-800 p-3 rounded-lg mb-4 text-sm font-medium">
                                    {{ session('success') }}
                                </div>
                            @endif
                            <form action="{{ route('books.review.store', $book->id) }}" method="POST">
                                @csrf
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Rating (1-5 Bintang)</label>
                                    <select name="rating" class="w-full sm:w-1/3 rounded-lg border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-indigo-500 dark:focus:border-indigo-500" required>
                                        <option value="5" {{ ($userReview->rating ?? '') == 5 ? 'selected' : '' }}>⭐⭐⭐⭐⭐ (5/5)</option>
                                        <option value="4" {{ ($userReview->rating ?? '') == 4 ? 'selected' : '' }}>⭐⭐⭐⭐ (4/5)</option>
                                        <option value="3" {{ ($userReview->rating ?? '') == 3 ? 'selected' : '' }}>⭐⭐⭐ (3/5)</option>
                                        <option value="2" {{ ($userReview->rating ?? '') == 2 ? 'selected' : '' }}>⭐⭐ (2/5)</option>
                                        <option value="1" {{ ($userReview->rating ?? '') == 1 ? 'selected' : '' }}>⭐ (1/5)</option>
                                    </select>
                                </div>
                                <div class="mb-4">
                                    <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-1">Komentar (Opsional)</label>
                                    <textarea name="comment" rows="3" class="w-full rounded-lg border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white shadow-sm focus:border-blue-500 focus:ring-blue-500 dark:focus:ring-indigo-500 dark:focus:border-indigo-500" placeholder="Bagaimana menurut Anda tentang buku ini?">{{ $userReview->comment ?? '' }}</textarea>
                                </div>
                                <button type="submit" class="px-6 py-2 bg-blue-600 dark:bg-indigo-600 text-white font-semibold rounded-lg shadow-sm hover:bg-blue-700 dark:hover:bg-indigo-500 transition">
                                    Simpan Ulasan
                                </button>
                            </form>
                        </div>
                    @endif

                    <div class="space-y-6">
                        @forelse($book->reviews as $review)
                            <div class="bg-gray-50 dark:bg-[#1a1d2e] rounded-xl p-5 border border-transparent dark:border-[#2d3147]">
                                <div class="flex items-center justify-between mb-2">
                                    <div class="font-semibold text-gray-900 dark:text-white">{{ $review->user->name }}</div>
                                    <div class="text-yellow-400 text-lg">
                                        {{ str_repeat('⭐', $review->rating) }}
                                    </div>
                                </div>
                                @if($review->comment)
                                    <p class="text-gray-600 dark:text-gray-300 italic">"{{ $review->comment }}"</p>
                                @endif
                                <div class="text-xs text-gray-400 dark:text-gray-500 mt-2">{{ $review->updated_at->diffForHumans() }}</div>
                            </div>
                        @empty
                            <p class="text-gray-500 dark:text-gray-400 text-center py-4">Belum ada ulasan untuk buku ini.</p>
                        @endforelse
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-public-layout>
