<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeData()" x-init="initTheme()" :class="{ 'dark': isDark }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Admin — Kelola Buku | P4I Publisher</title>
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

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-10">
            <div>
                <h1 class="text-3xl font-extrabold text-gray-900 dark:text-white tracking-tight">Kelola Buku</h1>
                <p class="text-gray-600 dark:text-gray-400 mt-1">Seluruh koleksi buku — published maupun draft.</p>
            </div>
            <a href="{{ route('admin.books.create') }}" class="inline-flex items-center gap-2 px-6 py-3 rounded-xl text-sm font-extrabold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 transition-all hover:-translate-y-0.5 shadow-lg shadow-indigo-200 dark:shadow-indigo-900/30 whitespace-nowrap">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 4v16m8-8H4"></path></svg>
                Upload Buku Baru
            </a>
        </div>

        {{-- Success / Error Alerts --}}
        @if(session('success'))
            <div class="mb-8 p-4 rounded-xl bg-emerald-50 dark:bg-emerald-500/10 border border-emerald-200 dark:border-emerald-500/20 text-emerald-700 dark:text-emerald-400 font-medium text-sm flex items-center gap-3">
                <svg class="w-5 h-5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                {{ session('success') }}
            </div>
        @endif

        {{-- Stats Cards (Analytics) --}}
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 mb-10">
            <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-5 flex items-center gap-4 shadow-sm transition-colors duration-300">
                <div class="w-12 h-12 bg-indigo-50 dark:bg-indigo-500/10 rounded-xl flex items-center justify-center shrink-0 border border-indigo-100 dark:border-transparent">
                    <svg class="w-6 h-6 text-indigo-600 dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                    <p class="text-xs text-gray-500 font-medium">Total Pendapatan</p>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-5 flex items-center gap-4 shadow-sm transition-colors duration-300">
                <div class="w-12 h-12 bg-emerald-50 dark:bg-emerald-500/10 rounded-xl flex items-center justify-center shrink-0 border border-emerald-100 dark:border-transparent">
                    <svg class="w-6 h-6 text-emerald-600 dark:text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $successfulOrders }}</p>
                    <p class="text-xs text-gray-500 font-medium">Total Buku Terjual</p>
                </div>
            </div>
            <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-5 flex items-center gap-4 shadow-sm transition-colors duration-300">
                <div class="w-12 h-12 bg-yellow-50 dark:bg-yellow-500/10 rounded-xl flex items-center justify-center shrink-0 border border-yellow-100 dark:border-transparent">
                    <svg class="w-6 h-6 text-yellow-600 dark:text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                </div>
                <div>
                    <p class="text-2xl font-black text-gray-900 dark:text-white">{{ $books->getCollection()->where('is_published', false)->count() }}</p>
                    <p class="text-xs text-gray-500 font-medium">Draft / Disembunyikan</p>
                </div>
            </div>
        </div>

        {{-- Sales Chart --}}
        <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl p-6 mb-10 shadow-sm transition-colors duration-300">
            <h2 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Statistik Penjualan (Tahun {{ date('Y') }})</h2>
            <div class="h-64 w-full">
                <canvas id="salesChart"></canvas>
            </div>
        </div>

        {{-- Chart.js Script --}}
        <script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
        <script>
            document.addEventListener("DOMContentLoaded", function() {
                const ctx = document.getElementById('salesChart').getContext('2d');
                new Chart(ctx, {
                    type: 'bar',
                    data: {
                        labels: ['Jan', 'Feb', 'Mar', 'Apr', 'Mei', 'Jun', 'Jul', 'Agu', 'Sep', 'Okt', 'Nov', 'Des'],
                        datasets: [{
                            label: 'Pendapatan (Rp)',
                            data: @json($chartData),
                            backgroundColor: 'rgba(99, 102, 241, 0.2)', // Indigo-500 with opacity
                            borderColor: 'rgba(99, 102, 241, 1)',
                            borderWidth: 2,
                            borderRadius: 6,
                            hoverBackgroundColor: 'rgba(99, 102, 241, 0.4)'
                        }]
                    },
                    options: {
                        responsive: true,
                        maintainAspectRatio: false,
                        plugins: {
                            legend: { display: false }
                        },
                        scales: {
                            y: {
                                beginAtZero: true,
                                grid: { color: 'rgba(156, 163, 175, 0.2)' },
                                ticks: { color: '#9ca3af' }
                            },
                            x: {
                                grid: { display: false },
                                ticks: { color: '#9ca3af' }
                            }
                        }
                    }
                });
            });
        </script>

        {{-- Books Table --}}
        <div class="bg-white dark:bg-[#1a1d2e] border border-gray-200 dark:border-[#2d3147] rounded-2xl overflow-hidden shadow-sm transition-colors duration-300">
            @if($books->isEmpty())
                <div class="py-24 text-center">
                    <svg class="w-16 h-16 text-gray-300 dark:text-gray-700 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                    <p class="text-gray-500 font-medium">Belum ada buku. <a href="{{ route('admin.books.create') }}" class="text-indigo-600 dark:text-indigo-400 hover:underline">Upload sekarang →</a></p>
                </div>
            @else
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-gray-200 dark:border-[#2d3147]">
                                <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Buku</th>
                                <th class="text-left px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest hidden md:table-cell">Harga</th>
                                <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest hidden lg:table-cell">Terjual</th>
                                <th class="text-center px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Status</th>
                                <th class="text-right px-6 py-4 text-xs font-bold text-gray-500 dark:text-gray-400 uppercase tracking-widest">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-gray-100 dark:divide-[#2d3147]">
                            @foreach($books as $book)
                                <tr class="hover:bg-gray-50 dark:hover:bg-white/[0.02] transition-colors group">
                                    {{-- Book Info --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center gap-4">
                                            {{-- Cover Thumbnail --}}
                                            <div class="w-10 h-14 rounded-lg overflow-hidden bg-gray-100 dark:bg-[#12141f] border border-gray-200 dark:border-[#2d3147] flex-shrink-0">
                                                @if($book->cover_image_path)
                                                    <img src="{{ $book->cover_image_path }}" alt="" class="w-full h-full object-cover">
                                                @else
                                                    <div class="w-full h-full flex items-center justify-center">
                                                        <svg class="w-4 h-4 text-gray-400 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                                    </div>
                                                @endif
                                            </div>
                                            <div class="min-w-0">
                                                <p class="font-bold text-gray-900 dark:text-white truncate max-w-[200px] lg:max-w-xs group-hover:text-indigo-600 dark:group-hover:text-indigo-300 transition-colors">{{ $book->title }}</p>
                                                <p class="text-xs text-gray-500 mt-0.5">{{ $book->author }}</p>
                                                <p class="text-[10px] text-gray-400 dark:text-gray-600 mt-0.5 font-mono">{{ $book->created_at->format('d M Y') }}</p>
                                            </div>
                                        </div>
                                    </td>

                                    {{-- Price --}}
                                    <td class="px-6 py-4 hidden md:table-cell">
                                        <span class="font-bold text-gray-900 dark:text-gray-200">Rp {{ number_format($book->price, 0, ',', '.') }}</span>
                                    </td>

                                    {{-- License Count --}}
                                    <td class="px-6 py-4 text-center hidden lg:table-cell">
                                        <div class="inline-flex items-center gap-1.5">
                                            <span class="w-7 h-7 rounded-full bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 font-extrabold text-xs flex items-center justify-center">{{ $book->active_licenses_count }}</span>
                                            <span class="text-xs text-gray-500">pembeli</span>
                                        </div>
                                    </td>

                                    {{-- Status Badge --}}
                                    <td class="px-6 py-4 text-center">
                                        @if($book->is_published)
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-emerald-500 dark:bg-emerald-400"></span>
                                                Published
                                            </span>
                                        @else
                                            <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full text-xs font-bold bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-500/20">
                                                <span class="w-1.5 h-1.5 rounded-full bg-yellow-500 dark:bg-yellow-400"></span>
                                                Draft
                                            </span>
                                        @endif
                                    </td>

                                    {{-- Actions --}}
                                    <td class="px-6 py-4">
                                        <div class="flex items-center justify-end gap-2">
                                            {{-- Toggle Publish --}}
                                            <form method="POST" action="{{ route('admin.books.togglePublish', $book) }}">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" title="{{ $book->is_published ? 'Sembunyikan dari Katalog' : 'Publikasikan' }}" class="px-3 py-1.5 rounded-lg text-xs font-bold transition-all {{ $book->is_published ? 'bg-yellow-50 dark:bg-yellow-500/10 text-yellow-600 dark:text-yellow-400 border border-yellow-200 dark:border-yellow-500/20 hover:bg-yellow-100 dark:hover:bg-yellow-500/20' : 'bg-emerald-50 dark:bg-emerald-500/10 text-emerald-600 dark:text-emerald-400 border border-emerald-200 dark:border-emerald-500/20 hover:bg-emerald-100 dark:hover:bg-emerald-500/20' }}">
                                                    {{ $book->is_published ? 'Sembunyikan' : 'Publikasikan' }}
                                                </button>
                                            </form>

                                            {{-- View in Catalog (if published) --}}
                                            @if($book->is_published)
                                                <a href="{{ route('books.show', $book->slug) }}" target="_blank" title="Lihat di Katalog" class="px-3 py-1.5 rounded-lg text-xs font-bold bg-indigo-50 dark:bg-indigo-500/10 text-indigo-600 dark:text-indigo-400 border border-indigo-200 dark:border-indigo-500/20 hover:bg-indigo-100 dark:hover:bg-indigo-500/20 transition-all">
                                                    Lihat
                                                </a>
                                            @endif
                                        </div>
                                    </td>
                                </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>

                {{-- Pagination --}}
                @if($books->hasPages())
                    <div class="px-6 py-5 border-t border-gray-200 dark:border-[#2d3147]">
                        {{ $books->links() }}
                    </div>
                @endif
            @endif
        </div>
    </div>
    <script>
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
