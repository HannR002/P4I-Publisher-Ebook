<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" x-data="themeData()" x-init="initTheme()" :class="{ 'dark': isDark }">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'P4I Digital Library') }}</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="font-sans text-gray-900 antialiased bg-[#f9fafb] dark:bg-[#0f1117] dark:text-gray-200 min-h-screen flex flex-col transition-colors duration-300" style="font-family: 'Inter', sans-serif;">
        
        <!-- Public Navigation Bar -->
        <div x-data="{ scrolled: false }" @scroll.window="scrolled = (window.pageYOffset > 20)" class="sticky top-0 z-50 w-full transition-all duration-300" :class="{ 'py-2': scrolled, 'py-0': !scrolled }">
            <nav :class="{ 'rounded-2xl shadow-lg border-gray-200/50 dark:border-gray-700/50 max-w-7xl mx-auto': scrolled, 'border-b border-gray-200 dark:border-[#2d3147]': !scrolled }" class="bg-white/80 dark:bg-[#1a1d2e]/80 backdrop-blur-lg transition-all duration-300">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center transition-all duration-300" :class="{ 'h-14': scrolled, 'h-16': !scrolled }">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="flex items-center gap-2 group">
                                <svg class="w-8 h-8 text-black dark:text-indigo-400 group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span class="font-extrabold text-xl tracking-wider text-black dark:text-white">P4I<span class="text-gray-500 dark:text-indigo-400"> Digital Library</span></span>
                            </a>
                        </div>

                        <div class="hidden xl:flex items-center gap-1 text-sm font-semibold text-gray-600 dark:text-gray-300">
                            <a href="{{ route('home') }}" class="px-2 py-2 hover:text-violet-600">Beranda</a>
                            <a href="{{ route('library.index') }}" class="px-2 py-2 hover:text-violet-600">Perpustakaan</a>
                            <a href="{{ route('library.index', ['type'=>'book']) }}" class="px-2 py-2 hover:text-violet-600">Buku</a>
                            <a href="{{ route('library.index', ['type'=>'journal']) }}" class="px-2 py-2 hover:text-violet-600">Jurnal</a>
                            <a href="{{ route('library.index', ['type'=>'article']) }}" class="px-2 py-2 hover:text-violet-600">Artikel</a>
                            <a href="{{ route('submission') }}" class="px-2 py-2 hover:text-violet-600">Penerbitan Buku</a>
                            <a href="{{ route('about') }}" class="px-2 py-2 hover:text-violet-600">Tentang</a>
                        </div>
                        
                        <!-- Right Menu -->
                        <div class="flex items-center gap-2 sm:gap-4">
                            <!-- Dark Mode Toggle -->
                            <button @click="toggleTheme()" class="p-2 rounded-full text-gray-500 hover:text-gray-900 hover:bg-gray-100 dark:text-gray-400 dark:hover:text-white dark:hover:bg-[#2d3147] transition-colors focus:outline-none">
                                <svg x-show="!isDark" class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.354 15.354A9 9 0 018.646 3.646 9.003 9.003 0 0012 21a9.003 9.003 0 008.354-5.646z"></path></svg>
                                <svg x-show="isDark" x-cloak class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 3v1m0 16v1m9-9h-1M4 12H3m15.364 6.364l-.707-.707M6.343 6.343l-.707-.707m12.728 0l-.707.707M6.343 17.657l-.707.707M16 12a4 4 0 11-8 0 4 4 0 018 0z"></path></svg>
                            </button>

                            @auth
                                <!-- Nav links for authenticated users -->
                                <div class="hidden md:flex items-center gap-1">
                                    <a href="{{ route('library.index') }}" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#2d3147] px-3 py-2 rounded-full transition-all">
                                        Perpustakaan
                                    </a>
                                    <a href="{{ route('my-library') }}" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#2d3147] px-3 py-2 rounded-full transition-all flex items-center gap-1.5">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        Perpustakaan
                                    </a>
                                    @if(Auth::check() && Auth::user()->isAuthor())
                                        <a href="{{ route('author.dashboard') }}" class="text-sm font-semibold text-indigo-600 dark:text-indigo-400 hover:text-indigo-800 dark:hover:text-indigo-300 bg-indigo-50 dark:bg-indigo-900/30 px-3 py-2 rounded-full transition-all">
                                            Portal Penulis
                                        </a>
                                    @else
                                        <a href="{{ route('author.register') }}" class="text-sm font-semibold text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white hover:bg-gray-100 dark:hover:bg-[#2d3147] px-3 py-2 rounded-full transition-all">
                                            Daftar Penulis
                                        </a>
                                    @endif
                                </div>

                                <!-- Dropdown untuk User Auth -->
                                <div class="hidden sm:flex sm:items-center">
                                    <x-dropdown align="right" width="52">
                                        <x-slot name="trigger">
                                            <button class="inline-flex items-center px-4 py-2 border border-gray-200 dark:border-[#2d3147] text-sm leading-4 font-bold rounded-full text-gray-700 dark:text-gray-200 bg-white dark:bg-[#12141f] hover:bg-gray-50 dark:hover:bg-[#1a1d2e] transition-all">
                                                <div>{{ Auth::user()->name }}</div>
                                                <div class="ms-2">
                                                    <svg class="fill-current h-4 w-4 text-gray-400" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <div class="bg-white/90 dark:bg-[#1a1d2e]/95 backdrop-blur-md border border-gray-100 dark:border-[#2d3147] rounded-xl py-2 mt-1 w-52">
                                                <x-dropdown-link :href="route('my-orders')" class="text-gray-700 dark:text-gray-300 hover:bg-gray-50 dark:hover:bg-[#2d3147] hover:text-black dark:hover:text-white font-medium px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                    Riwayat Transaksi
                                                </x-dropdown-link>

                                                @if(Auth::user()->is_admin)
                                                    <div class="my-1 mx-3 border-t border-gray-100 dark:border-[#2d3147]"></div>
                                                    <x-dropdown-link :href="route('admin.books.index')" class="text-indigo-600 dark:text-indigo-400 hover:bg-indigo-50 dark:hover:bg-indigo-500/10 font-bold px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                        Kelola Buku (Admin)
                                                    </x-dropdown-link>
                                                @endif

                                                <div class="my-1 mx-3 border-t border-gray-100 dark:border-[#2d3147]"></div>
                                                <form method="POST" action="{{ route('logout') }}">
                                                    @csrf
                                                    <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-red-600 dark:text-red-400 hover:bg-red-50 dark:hover:bg-red-500/10 font-medium px-4 py-2 mx-1 rounded-lg transition-colors">
                                                        Log Out
                                                    </x-dropdown-link>
                                                </form>
                                            </div>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            @else
                                <a href="{{ route('library.index') }}" class="hidden sm:block text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white transition-colors px-3 py-1.5 rounded-full hover:bg-gray-100 dark:hover:bg-[#2d3147]">
                                    Perpustakaan
                                </a>
                                <a href="{{ route('login') }}" class="text-xs font-semibold text-gray-600 dark:text-gray-300 hover:text-black dark:hover:text-white transition-colors">Masuk</a>
                                <a href="{{ route('register') }}" class="text-xs font-bold text-white bg-black dark:bg-indigo-600 hover:bg-gray-800 dark:hover:bg-indigo-500 px-4 py-1.5 rounded-full transition-all">Mulai</a>
                            @endauth
                        </div>
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-grow w-full relative">
            {{ $slot }}
        </main>
        
        <!-- Standard Complete Footer -->
        <footer class="bg-white dark:bg-[#1a1d2e] border-t border-gray-200 dark:border-[#2d3147] mt-20 pt-16 pb-8 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <!-- Column 1: About -->
                    <div class="col-span-1 md:col-span-1">
                        <a href="/" class="flex items-center gap-2 mb-4">
                            <svg class="w-8 h-8 text-black dark:text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="font-extrabold text-xl tracking-wider text-black dark:text-white">P4I<span class="text-gray-500 dark:text-indigo-400"> Digital Library</span></span>
                        </a>
                        <p class="text-sm text-gray-500 dark:text-gray-400 leading-relaxed mb-6">
                            Perpustakaan digital dan pusat penerbitan buku P4I untuk buku, jurnal, artikel, dan publikasi ilmiah.
                        </p>
                    </div>

                    <!-- Column 2: Tautan Cepat -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Eksplorasi</h4>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li><a href="{{ route('books.index') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Katalog Buku</a></li>
                            <li><a href="{{ route('books.index') }}?sort=popular" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Buku Terpopuler</a></li>
                            <li><a href="{{ route('books.index') }}?sort=latest" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Rilis Terbaru</a></li>
                            <li><a href="{{ route('books.index') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Kategori E-Book</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Dukungan -->
                    <div>
                        <h4 class="text-sm font-bold text-gray-900 dark:text-white uppercase tracking-wider mb-4">Dukungan</h4>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li><a href="{{ route('help-center') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Pusat Bantuan</a></li>
                            <li><a href="{{ route('how-to-buy') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Cara Pembelian</a></li>
                            <li><a href="{{ route('privacy-policy') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Kebijakan Privasi</a></li>
                            <li><a href="{{ route('terms') }}" class="hover:text-blue-600 dark:hover:text-indigo-400 transition-colors">Syarat & Ketentuan</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Hubungi Kami -->
                    <div>
                        <h3 class="font-bold text-gray-900 dark:text-white tracking-wider mb-4 uppercase text-sm">Hubungi Kami</h3>
                        <ul class="space-y-3 text-sm text-gray-600 dark:text-gray-400">
                            <li>
                                <a href="mailto:admin@p4ijournal.org" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    admin@p4ijournal.org
                                </a>
                            </li>
                            <li>
                                <a href="https://wa.me/6289699161526" target="_blank" class="hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    +62 896-9916-1526
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sub-footer (Vogue Style) -->
                <div class="border-t border-gray-200 dark:border-[#2d3147] pt-8 mt-12 flex flex-col gap-6">
                    <!-- Social Icons -->
                    <div class="flex items-center justify-center gap-6">
                        <div class="flex gap-4">
                            <a href="https://www.linkedin.com/in/farhanmuhammad-id/" target="_blank" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span class="sr-only">LinkedIn</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M19 0h-14c-2.761 0-5 2.239-5 5v14c0 2.761 2.239 5 5 5h14c2.762 0 5-2.239 5-5v-14c0-2.761-2.238-5-5-5zm-11 19h-3v-11h3v11zm-1.5-12.268c-.966 0-1.75-.79-1.75-1.764s.784-1.764 1.75-1.764 1.75.79 1.75 1.764-.783 1.764-1.75 1.764zm13.5 12.268h-3v-5.604c0-3.368-4-3.113-4 0v5.604h-3v-11h3v1.765c1.396-2.586 7-2.777 7 2.476v6.759z"/></svg>
                            </a>
                            <a href="https://github.com/HannR002" target="_blank" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span class="sr-only">GitHub</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12 2C6.477 2 2 6.484 2 12.017c0 4.425 2.865 8.18 6.839 9.504.5.092.682-.217.682-.483 0-.237-.008-.868-.013-1.703-2.782.605-3.369-1.343-3.369-1.343-.454-1.158-1.11-1.466-1.11-1.466-.908-.62.069-.608.069-.608 1.003.07 1.531 1.032 1.531 1.032.892 1.53 2.341 1.088 2.91.832.092-.647.35-1.088.636-1.338-2.22-.253-4.555-1.113-4.555-4.951 0-1.093.39-1.988 1.029-2.688-.103-.253-.446-1.272.098-2.65 0 0 .84-.27 2.75 1.026A9.564 9.564 0 0112 6.844c.85.004 1.705.115 2.504.337 1.909-1.296 2.747-1.027 2.747-1.027.546 1.379.202 2.398.1 2.651.64.7 1.028 1.595 1.028 2.688 0 3.848-2.339 4.695-4.566 4.943.359.309.678.92.678 1.855 0 1.338-.012 2.419-.012 2.747 0 .268.18.58.688.482A10.019 10.019 0 0022 12.017C22 6.484 17.522 2 12 2z" clip-rule="evenodd"/></svg>
                            </a>
                            <a href="https://instagram.com/p4i.official" target="_blank" class="text-gray-400 hover:text-indigo-600 dark:hover:text-indigo-400 transition-colors">
                                <span class="sr-only">Instagram</span>
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path fill-rule="evenodd" d="M12.315 2c2.43 0 2.784.013 3.808.06 1.064.049 1.791.218 2.427.465a4.902 4.902 0 011.772 1.153 4.902 4.902 0 011.153 1.772c.247.636.416 1.363.465 2.427.048 1.067.06 1.407.06 4.123v.08c0 2.643-.012 2.987-.06 4.043-.049 1.064-.218 1.791-.465 2.427a4.902 4.902 0 01-1.153 1.772 4.902 4.902 0 01-1.772 1.153c-.636.247-1.363.416-2.427.465-1.067.048-1.407.06-4.123.06h-.08c-2.643 0-2.987-.012-4.043-.06-1.064-.049-1.791-.218-2.427-.465a4.902 4.902 0 01-1.772-1.153 4.902 4.902 0 01-1.153-1.772c-.247-.636-.416-1.363-.465-2.427-.047-1.024-.06-1.379-.06-3.808v-.63c0-2.43.013-2.784.06-3.808.049-1.064.218-1.791.465-2.427a4.902 4.902 0 011.153-1.772A4.902 4.902 0 015.45 2.525c.636-.247 1.363-.416 2.427-.465C8.901 2.013 9.256 2 11.685 2h.63zm-.081 1.802h-.468c-2.456 0-2.784.011-3.807.058-.975.045-1.504.207-1.857.344-.467.182-.8.398-1.15.748-.35.35-.566.683-.748 1.15-.137.353-.3.882-.344 1.857-.047 1.023-.058 1.351-.058 3.807v.468c0 2.456.011 2.784.058 3.807.045.975.207 1.504.344 1.857.182.466.399.8.748 1.15.35.35.683.566 1.15.748.353.137.882.3 1.857.344 1.054.048 1.37.058 4.041.058h.08c2.597 0 2.917-.01 3.96-.058.976-.045 1.505-.207 1.858-.344.466-.182.8-.398 1.15-.748.35-.35.566-.683.748-1.15.137-.353.3-.882.344-1.857.048-1.055.058-1.37.058-4.041v-.08c0-2.597-.01-2.917-.058-3.96-.045-.976-.207-1.505-.344-1.858a3.097 3.097 0 00-.748-1.15 3.098 3.098 0 00-1.15-.748c-.353-.137-.882-.3-1.857-.344-1.023-.047-1.351-.058-3.807-.058zM12 6.865a5.135 5.135 0 110 10.27 5.135 5.135 0 010-10.27zm0 1.802a3.333 3.333 0 100 6.666 3.333 3.333 0 000-6.666zm5.338-3.205a1.2 1.2 0 110 2.4 1.2 1.2 0 010-2.4z" clip-rule="evenodd"/></svg>
                            </a>
                        </div>
                    </div>
                    
                    <!-- Sub-footer Links -->
                    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2">
                        <a href="{{ url('/about') }}" class="text-[10px] font-bold tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">About Us</a>
                        <a href="{{ url('/contact') }}" class="text-[10px] font-bold tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">Contact</a>
                        <a href="{{ url('/submission') }}" class="text-[10px] font-bold tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">Submission</a>
                        <a href="#" class="text-[10px] font-bold tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">Privacy Policy</a>
                        <a href="#" class="text-[10px] font-bold tracking-widest uppercase text-gray-500 hover:text-gray-900 dark:hover:text-white transition-colors">Terms of Service</a>
                    </div>

                    <!-- Copyright -->
                    <div class="text-center">
                        <p class="text-[10px] text-gray-400 dark:text-gray-500">
                            &copy; {{ date('Y') }} P4I Publisher. All rights reserved.
                        </p>
                    </div>
                </div>
            </div>
        </footer>

        <!-- Scroll to Top Button (Brown Bookmark) -->
        <button x-data="{ scrolled: false }"
                @scroll.window="scrolled = (window.pageYOffset > 300)"
                x-show="scrolled"
                x-transition:enter="transition ease-out duration-300"
                x-transition:enter-start="opacity-0 transform translate-y-4"
                x-transition:enter-end="opacity-100 transform translate-y-0"
                x-transition:leave="transition ease-in duration-300"
                x-transition:leave-start="opacity-100 transform translate-y-0"
                x-transition:leave-end="opacity-0 transform translate-y-4"
                @click="window.scrollTo({top: 0, behavior: 'smooth'})"
                class="fixed bottom-8 right-8 z-50 flex items-center justify-center w-12 h-16 bg-[#8B5A2B] hover:bg-[#6b441f] text-white shadow-lg transition-colors overflow-hidden group focus:outline-none"
                style="clip-path: polygon(0 0, 100% 0, 100% 100%, 50% 85%, 0 100%);">
            <svg class="w-6 h-6 mb-2 group-hover:-translate-y-1 transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M5 15l7-7 7 7"/></svg>
        </button>

        <!-- Theme Toggle Logic -->
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
