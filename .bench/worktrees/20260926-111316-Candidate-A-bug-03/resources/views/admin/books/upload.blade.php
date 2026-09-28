<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeData()" x-init="initTheme()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Upload Buku | P4I Publisher</title>
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="font-sans antialiased bg-gray-50 dark:bg-[#0f1117] text-gray-900 dark:text-gray-200 min-h-screen transition-colors duration-300" style="font-family: 'Inter', sans-serif;">

    {{-- Admin Navbar --}}
    <nav class="bg-white dark:bg-[#1a1d2e] border-b border-gray-200 dark:border-[#2d3147] sticky top-0 z-50 transition-colors duration-300">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex justify-between items-center h-16">
                <div class="flex items-center gap-6">
                    <a href="{{ route('admin.books.index') }}" class="flex items-center gap-2 group">
                        <svg class="w-7 h-7 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="font-extrabold text-lg tracking-wider text-black dark:text-white">P4I<span class="text-indigo-600 dark:text-indigo-400"> E-Book</span></span>
                    </a>
                    <span class="hidden sm:block text-gray-300 dark:text-[#2d3147] text-xl font-thin">|</span>
                    <span class="hidden sm:block text-sm font-semibold text-indigo-600 dark:text-indigo-300 tracking-widest uppercase">Admin Panel</span>
                </div>
                <div class="flex items-center gap-4">
                    <!-- Dark Mode Toggle -->
                    <button @click="toggleTheme()" class="p-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-[#2d3147] transition-colors focus:outline-none">
                        <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                        <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                    </button>
                    <a href="{{ route('books.index') }}" class="text-sm text-gray-600 dark:text-gray-400 hover:text-black dark:hover:text-white transition-colors">← Lihat Katalog</a>
                    <form method="POST" action="{{ route('logout') }}">
                        @csrf
                        <button type="submit" class="text-sm font-semibold text-gray-600 dark:text-gray-400 hover:text-red-600 dark:hover:text-red-400 transition-colors">Log Out</button>
                    </form>
                </div>
            </div>
        </div>
    </nav>

    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
        {{-- Breadcrumb --}}
        <div class="flex items-center gap-2 text-sm text-gray-500 dark:text-gray-500 mb-8">
            <a href="{{ route('admin.books.index') }}" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">Kelola Buku</a>
            <span>/</span>
            <span class="text-gray-700 dark:text-gray-300">Upload Buku Baru</span>
        </div>

        {{-- Page Title --}}
        <div class="mb-10">
            <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Upload Buku Baru</h1>
            <p class="text-gray-600 dark:text-gray-400 mt-1">File PDF akan disimpan secara privat & aman di server.</p>
        </div>

        {{-- Success Alert --}}
        @if(session('success'))
            <div class="mb-8 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-medium text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Error Alert --}}
        @if($errors->any())
            <div class="mb-8 p-4 rounded-xl bg-red-50 dark:bg-red-500/10 border border-red-200 dark:border-red-500/20 text-red-700 dark:text-red-400 font-medium text-sm">
                <ul class="space-y-1">
                    @foreach($errors->all() as $error)
                        <li>• {{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        {{-- Upload Form --}}
        <form action="{{ route('admin.books.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                {{-- LEFT COLUMN --}}
                <div class="space-y-5">
                    {{-- Judul Buku --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="title" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Judul Buku <span class="text-red-500 dark:text-red-400">*</span></label>
                        <input
                            type="text" id="title" name="title"
                            value="{{ old('title') }}"
                            required
                            placeholder="Contoh: Pengantar Kecerdasan Buatan"
                            class="w-full bg-white dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] text-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors placeholder-gray-400 dark:placeholder-gray-600 shadow-sm"
                        >
                    </div>

                    {{-- Penulis --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="author" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Nama Penulis <span class="text-red-500 dark:text-red-400">*</span></label>
                        <input
                            type="text" id="author" name="author"
                            value="{{ old('author') }}"
                            required
                            placeholder="Contoh: Dr. Ahmad Fuad, M.Kom"
                            class="w-full bg-white dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] text-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors placeholder-gray-400 dark:placeholder-gray-600 shadow-sm"
                        >
                    </div>

                    {{-- Harga --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="price" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Harga (IDR) <span class="text-red-500 dark:text-red-400">*</span></label>
                        <div class="relative">
                            <span class="absolute left-4 top-1/2 -translate-y-1/2 text-gray-500 dark:text-gray-400 font-bold text-sm">Rp</span>
                            <input
                                type="number" id="price" name="price"
                                value="{{ old('price') }}"
                                required min="0" step="1000"
                                placeholder="75000"
                                class="w-full bg-white dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] text-gray-900 dark:text-white rounded-xl pl-12 pr-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors placeholder-gray-400 dark:placeholder-gray-600 shadow-sm"
                            >
                        </div>
                    </div>

                    {{-- Kategori --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="categories" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Kategori / Tag</label>
                        <input
                            type="text" id="categories" name="categories"
                            value="{{ old('categories') }}"
                            placeholder="Pisahkan dengan koma. Contoh: Teknologi, Fiksi"
                            class="w-full bg-white dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] text-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors placeholder-gray-400 dark:placeholder-gray-600 shadow-sm"
                        >
                    </div>

                    {{-- Status Publish --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label class="flex items-center gap-4 cursor-pointer group">
                            <div class="relative">
                                <input type="checkbox" id="is_published" name="is_published" value="1" class="sr-only peer" {{ old('is_published') ? 'checked' : '' }}>
                                <div class="w-11 h-6 bg-gray-200 dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] rounded-full peer-checked:bg-indigo-600 transition-colors"></div>
                                <div class="absolute top-0.5 left-0.5 w-5 h-5 bg-white dark:bg-gray-600 border border-gray-300 dark:border-transparent rounded-full transition-all peer-checked:translate-x-5 peer-checked:bg-white peer-checked:border-white shadow-sm"></div>
                            </div>
                            <div>
                                <p class="text-sm font-bold text-gray-900 dark:text-gray-300">Publikasikan Sekarang</p>
                                <p class="text-xs text-gray-500 mt-0.5">Buku langsung muncul di katalog publik</p>
                            </div>
                        </label>
                    </div>
                </div>

                {{-- RIGHT COLUMN --}}
                <div class="space-y-5">
                    {{-- Deskripsi --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="description" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Deskripsi Buku</label>
                        <textarea
                            id="description" name="description"
                            rows="5"
                            placeholder="Tuliskan sinopsis atau deskripsi singkat buku ini..."
                            class="w-full bg-white dark:bg-[#12141f] border border-gray-300 dark:border-[#2d3147] text-gray-900 dark:text-white rounded-xl px-4 py-3 text-sm focus:border-indigo-500 focus:ring-indigo-500 focus:ring-1 transition-colors placeholder-gray-400 dark:placeholder-gray-600 resize-none shadow-sm"
                        >{{ old('description') }}</textarea>
                    </div>

                    {{-- Cover Image --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="cover_image" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">Cover Buku <span class="text-gray-400 dark:text-gray-500 normal-case font-normal">(Opsional, JPG/PNG/WebP, maks. 5MB)</span></label>
                        <div class="bg-gray-50 dark:bg-transparent border-2 border-dashed border-gray-300 dark:border-[#2d3147] hover:border-indigo-500 dark:hover:border-indigo-500/50 rounded-xl p-6 text-center transition-colors group cursor-pointer" onclick="document.getElementById('cover_image').click()">
                            <svg class="w-10 h-10 text-gray-400 dark:text-gray-600 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 mx-auto mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"></path></svg>
                            <p class="text-xs text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Klik atau seret gambar ke sini</p>
                            <input type="file" id="cover_image" name="cover_image" accept="image/jpeg,image/png,image/jpg,image/webp" class="hidden">
                        </div>
                        <p id="cover_filename" class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 hidden"></p>
                    </div>

                    {{-- PDF File --}}
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 shadow-sm transition-colors duration-300">
                        <label for="pdf_file" class="block text-sm font-bold text-gray-700 dark:text-gray-300 mb-2 uppercase tracking-wider">File PDF <span class="text-red-500 dark:text-red-400">*</span> <span class="text-gray-400 dark:text-gray-500 normal-case font-normal">(Maks. 50MB)</span></label>
                        <div class="bg-gray-50 dark:bg-transparent border-2 border-dashed border-gray-300 dark:border-[#2d3147] hover:border-indigo-500 dark:hover:border-indigo-500/50 rounded-xl p-6 text-center transition-colors group cursor-pointer" onclick="document.getElementById('pdf_file').click()">
                            <svg class="w-10 h-10 text-gray-400 dark:text-gray-600 group-hover:text-indigo-500 dark:group-hover:text-indigo-400 mx-auto mb-2 transition-colors" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                            <p class="text-xs text-gray-500 group-hover:text-gray-700 dark:group-hover:text-gray-300 transition-colors">Klik atau seret file PDF ke sini</p>
                            <input type="file" id="pdf_file" name="pdf_file" accept="application/pdf" required class="hidden">
                        </div>
                        <p id="pdf_filename" class="text-xs text-indigo-600 dark:text-indigo-400 mt-2 hidden"></p>
                    </div>
                </div>
            </div>

            {{-- Submit Button --}}
            <div class="flex justify-end pt-2">
                <button type="submit" class="inline-flex items-center gap-2 px-8 py-4 rounded-xl text-base font-extrabold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 transition-all hover:-translate-y-0.5 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 16a4 4 0 01-.88-7.903A5 5 0 1115.9 6L16 6a5 5 0 011 9.9M15 13l-3-3m0 0l-3 3m3-3v12"></path></svg>
                    Upload Buku
                </button>
            </div>
        </form>
    </div>

    <script>
        // Show selected filename for PDF & cover
        document.getElementById('pdf_file').addEventListener('change', function() {
            const el = document.getElementById('pdf_filename');
            if (this.files[0]) {
                el.textContent = '✓ ' + this.files[0].name;
                el.classList.remove('hidden');
            }
        });
        document.getElementById('cover_image').addEventListener('change', function() {
            const el = document.getElementById('cover_filename');
            if (this.files[0]) {
                el.textContent = '✓ ' + this.files[0].name;
                el.classList.remove('hidden');
            }
        });

        function themeData() {
            return {
                isDark: false,
                initTheme() {
                    if (localStorage.getItem('theme') === 'dark' || (!('theme' in localStorage) && window.matchMedia('(prefers-color-scheme: dark)').matches)) {
                        this.isDark = true;
                    } else {
                        this.isDark = false;
                    }
                },
                toggleTheme() {
                    this.isDark = !this.isDark;
                    localStorage.setItem('theme', this.isDark ? 'dark' : 'light');
                }
            }
        }
    </script>
</body>
</html>
