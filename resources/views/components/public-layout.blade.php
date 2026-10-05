<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
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
        <!-- Theme Initialization (Prevents FOUC) -->
        <x-theme-init />
    </head>
    <body class="font-sans antialiased bg-background text-text-primary min-h-screen flex flex-col transition-colors duration-300">

        <!-- Public Navigation Bar -->
        <div x-data="{ scrolled: false, mobileMenuOpen: false }" @scroll.window="scrolled = (window.pageYOffset > 20)" class="sticky top-0 z-50 w-full transition-all duration-300" :class="{ 'py-2': scrolled, 'py-0': !scrolled }">
            <nav :class="{ 'rounded-2xl shadow-lg border-border/50 max-w-7xl mx-auto': scrolled, 'border-b border-border': !scrolled }" class="bg-surface/80 backdrop-blur-lg transition-all duration-300 relative">
                <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                    <div class="flex justify-between items-center transition-all duration-300" :class="{ 'h-14': scrolled, 'h-16': !scrolled }">
                        <!-- Logo -->
                        <div class="flex-shrink-0 flex items-center">
                            <a href="{{ route('home') }}" class="flex items-center gap-2 group focus-ring rounded-lg">
                                <svg class="w-8 h-8 text-text-primary group-hover:scale-110 transition-transform duration-300" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                                <span class="font-extrabold text-xl tracking-wider text-text-primary">P4I<span class="text-text-muted"> Digital Library</span></span>
                            </a>
                        </div>

                        <!-- Desktop Menu -->
                        <div class="hidden xl:flex items-center gap-1 text-sm font-semibold text-text-secondary">
                            <a href="{{ route('home') }}" class="px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg {{ request()->routeIs('home') ? 'text-primary' : '' }}">Beranda</a>
                            
                            <!-- Perpustakaan Dropdown -->
                            <div class="relative" x-data="{ open: false }" @click.outside="open = false" @close.stop="open = false">
                                <button @click="open = ! open" class="inline-flex items-center px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg {{ request()->routeIs('library.*') || request()->routeIs('books.*') || request()->routeIs('journals.*') || request()->routeIs('articles.*') ? 'text-primary' : '' }}">
                                    Perpustakaan
                                    <svg class="ml-1 h-4 w-4 fill-current" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                    </svg>
                                </button>
                                <div x-show="open"
                                        x-transition:enter="transition ease-out duration-200"
                                        x-transition:enter-start="opacity-0 scale-95"
                                        x-transition:enter-end="opacity-100 scale-100"
                                        x-transition:leave="transition ease-in duration-75"
                                        x-transition:leave-start="opacity-100 scale-100"
                                        x-transition:leave-end="opacity-0 scale-95"
                                        class="absolute z-50 mt-2 w-48 rounded-xl shadow-lg origin-top-left left-0"
                                        style="display: none;"
                                        @click="open = false">
                                    <div class="rounded-xl overflow-hidden py-2 bg-surface backdrop-blur-md border border-border">
                                        <a href="{{ route('library.index') }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-surface-muted {{ request()->fullUrl() === route('library.index') ? 'font-bold text-primary' : '' }}">Semua Koleksi</a>
                                        <a href="{{ route('library.index', ['type'=>'book']) }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-surface-muted {{ request('type') === 'book' ? 'font-bold text-primary' : '' }}">Buku</a>
                                        <a href="{{ route('library.index', ['type'=>'journal']) }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-surface-muted {{ request('type') === 'journal' ? 'font-bold text-primary' : '' }}">Jurnal</a>
                                        <a href="{{ route('library.index', ['type'=>'article']) }}" class="block px-4 py-2 text-sm text-text-primary hover:bg-surface-muted {{ request('type') === 'article' ? 'font-bold text-primary' : '' }}">Artikel</a>
                                    </div>
                                </div>
                            </div>
                            
                            <a href="{{ route('submission') }}" class="px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg {{ request()->routeIs('submission') ? 'text-primary' : '' }}">Penerbitan Buku</a>
                            <a href="{{ route('about') }}" class="px-3 py-2 hover:text-primary transition-colors focus-ring rounded-lg {{ request()->routeIs('about') ? 'text-primary' : '' }}">Tentang</a>
                        </div>

                        <!-- Right Actions -->
                        <div class="flex items-center gap-2 sm:gap-4">
                            <!-- Theme Switcher -->
                            <x-theme-switcher />

                            @auth
                                <!-- Dropdown untuk User Auth -->
                                <div class="hidden sm:flex sm:items-center">
                                    <x-dropdown align="right" width="52">
                                        <x-slot name="trigger">
                                            <button class="inline-flex items-center px-4 py-2 border border-border text-sm leading-4 font-bold rounded-full text-text-primary bg-surface hover:bg-surface-muted transition-all focus-ring">
                                                <div>{{ Auth::user()->name }}</div>
                                                <div class="ms-2">
                                                    <svg class="fill-current h-4 w-4 text-text-muted" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                                        <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                                    </svg>
                                                </div>
                                            </button>
                                        </x-slot>

                                        <x-slot name="content">
                                            <x-dropdown-link :href="route('my-library')" class="text-text-primary hover:bg-surface-muted font-medium px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                Perpustakaan Saya
                                            </x-dropdown-link>
                                            <x-dropdown-link :href="route('my-orders')" class="text-text-primary hover:bg-surface-muted font-medium px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                Riwayat Transaksi
                                            </x-dropdown-link>

                                            @if(Auth::check() && Auth::user()->isAuthor())
                                                <div class="my-1 mx-3 border-t border-border"></div>
                                                <x-dropdown-link :href="route('author.dashboard')" class="text-primary hover:bg-primary-subtle font-bold px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                    Portal Penulis
                                                </x-dropdown-link>
                                            @endif

                                            @if(Auth::user()->is_admin)
                                                <div class="my-1 mx-3 border-t border-border"></div>
                                                <x-dropdown-link :href="route('admin.books.index')" class="text-primary hover:bg-primary-subtle font-bold px-4 py-2 mx-1 rounded-lg transition-colors flex items-center gap-2">
                                                    Kelola Buku (Admin)
                                                </x-dropdown-link>
                                            @endif

                                            <div class="my-1 mx-3 border-t border-border"></div>
                                            <form method="POST" action="{{ route('logout') }}">
                                                @csrf
                                                <x-dropdown-link :href="route('logout')" onclick="event.preventDefault(); this.closest('form').submit();" class="text-danger hover:bg-surface-muted font-medium px-4 py-2 mx-1 rounded-lg transition-colors">
                                                    Log Out
                                                </x-dropdown-link>
                                            </form>
                                        </x-slot>
                                    </x-dropdown>
                                </div>
                            @else
                                <a href="{{ route('login') }}" class="hidden sm:inline-block text-sm font-semibold text-text-secondary hover:text-text-primary transition-colors focus-ring rounded-lg px-3 py-2">Masuk</a>
                                <a href="{{ route('register') }}" class="hidden sm:inline-block text-sm font-bold text-primary-foreground bg-primary hover:bg-primary-hover px-4 py-2 rounded-full transition-all focus-ring">Daftar</a>
                            @endauth

                            <!-- Mobile Menu Button -->
                            <button @click="mobileMenuOpen = !mobileMenuOpen" class="xl:hidden p-2 rounded-lg text-text-secondary hover:bg-surface-muted focus-ring">
                                <svg x-show="!mobileMenuOpen" class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"></path></svg>
                                <svg x-show="mobileMenuOpen" x-cloak class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"></path></svg>
                            </button>
                        </div>
                    </div>
                </div>

                <!-- Mobile Menu -->
                <div x-show="mobileMenuOpen" x-cloak
                     x-transition:enter="transition ease-out duration-200"
                     x-transition:enter-start="opacity-0 -translate-y-2"
                     x-transition:enter-end="opacity-100 translate-y-0"
                     x-transition:leave="transition ease-in duration-150"
                     x-transition:leave-start="opacity-100 translate-y-0"
                     x-transition:leave-end="opacity-0 -translate-y-2"
                     class="xl:hidden absolute top-full left-0 w-full bg-surface border-b border-border shadow-lg">
                    <div class="px-4 pt-2 pb-6 space-y-1">
                        <a href="{{ route('home') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-text-primary hover:bg-surface-muted">Beranda</a>
                        
                        <div x-data="{ libraryOpen: false }" class="space-y-1">
                            <button @click="libraryOpen = !libraryOpen" class="w-full text-left flex justify-between items-center px-3 py-2 rounded-lg text-base font-semibold text-text-primary hover:bg-surface-muted">
                                Perpustakaan
                                <svg class="w-4 h-4 transition-transform duration-200" :class="{'rotate-180': libraryOpen}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"></path></svg>
                            </button>
                            <div x-show="libraryOpen" x-cloak class="pl-4 space-y-1 pb-2">
                                <a href="{{ route('library.index') }}" class="block px-3 py-2 rounded-lg text-base font-medium text-text-secondary hover:text-text-primary hover:bg-surface-muted">Semua Koleksi</a>
                                <a href="{{ route('library.index', ['type'=>'book']) }}" class="block px-3 py-2 rounded-lg text-base font-medium text-text-secondary hover:text-text-primary hover:bg-surface-muted">Buku</a>
                                <a href="{{ route('library.index', ['type'=>'journal']) }}" class="block px-3 py-2 rounded-lg text-base font-medium text-text-secondary hover:text-text-primary hover:bg-surface-muted">Jurnal</a>
                                <a href="{{ route('library.index', ['type'=>'article']) }}" class="block px-3 py-2 rounded-lg text-base font-medium text-text-secondary hover:text-text-primary hover:bg-surface-muted">Artikel</a>
                            </div>
                        </div>

                        <a href="{{ route('submission') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-text-primary hover:bg-surface-muted">Penerbitan Buku</a>
                        <a href="{{ route('about') }}" class="block px-3 py-2 rounded-lg text-base font-semibold text-text-primary hover:bg-surface-muted">Tentang</a>

                        @guest
                            <div class="pt-4 mt-2 border-t border-border flex flex-col gap-2">
                                <a href="{{ route('login') }}" class="block px-3 py-2 text-center rounded-lg text-base font-semibold text-text-secondary border border-border">Masuk</a>
                                <a href="{{ route('register') }}" class="block px-3 py-2 text-center rounded-lg text-base font-bold text-primary-foreground bg-primary">Daftar</a>
                            </div>
                        @endguest
                    </div>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <main class="flex-grow w-full relative">
            {{ $slot }}
        </main>

        <!-- Standard Complete Footer -->
        <footer class="bg-surface border-t border-border mt-20 pt-16 pb-8 transition-colors duration-300">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="grid grid-cols-1 md:grid-cols-4 gap-12 mb-12">
                    <!-- Column 1: About -->
                    <div class="col-span-1 md:col-span-1">
                        <a href="{{ route('home') }}" class="flex items-center gap-2 mb-4">
                            <svg class="w-8 h-8 text-text-primary" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            <span class="font-extrabold text-xl tracking-wider text-text-primary">P4I<span class="text-text-muted"> Digital Library</span></span>
                        </a>
                        <p class="text-sm text-text-secondary leading-relaxed mb-6">
                            Perpustakaan digital dan pusat penerbitan P4I untuk buku, jurnal, artikel, dan publikasi ilmiah.
                        </p>
                    </div>

                    <!-- Column 2: Eksplorasi -->
                    <div>
                        <h4 class="text-sm font-bold text-text-primary uppercase tracking-wider mb-4">Perpustakaan</h4>
                        <ul class="space-y-3 text-sm text-text-secondary">
                            <li><a href="{{ route('library.index') }}" class="hover:text-primary transition-colors">Semua Koleksi</a></li>
                            <li><a href="{{ route('library.index', ['type'=>'book']) }}" class="hover:text-primary transition-colors">Buku</a></li>
                            <li><a href="{{ route('library.index', ['type'=>'journal']) }}" class="hover:text-primary transition-colors">Jurnal</a></li>
                            <li><a href="{{ route('library.index', ['type'=>'article']) }}" class="hover:text-primary transition-colors">Artikel</a></li>
                        </ul>
                    </div>

                    <!-- Column 3: Penerbitan -->
                    <div>
                        <h4 class="text-sm font-bold text-text-primary uppercase tracking-wider mb-4">Penerbitan</h4>
                        <ul class="space-y-3 text-sm text-text-secondary">
                            <li><a href="{{ route('submission') }}" class="hover:text-primary transition-colors">Terbitkan Buku</a></li>
                            <li><a href="{{ route('author.register') }}" class="hover:text-primary transition-colors">Daftar Penulis</a></li>
                            <li><a href="{{ route('author.dashboard') }}" class="hover:text-primary transition-colors">Portal Penulis</a></li>
                        </ul>
                    </div>

                    <!-- Column 4: Hubungi Kami -->
                    <div>
                        <h3 class="font-bold text-text-primary tracking-wider mb-4 uppercase text-sm">Hubungi Kami</h3>
                        <ul class="space-y-3 text-sm text-text-secondary">
                            <li>
                                <a href="mailto:admin@p4ijournal.org" class="hover:text-primary transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path></svg>
                                    admin@p4ijournal.org
                                </a>
                            </li>
                            <li>
                                <a href="https://wa.me/6289699161526" target="_blank" class="hover:text-primary transition-colors flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 5a2 2 0 012-2h3.28a1 1 0 01.948.684l1.498 4.493a1 1 0 01-.502 1.21l-2.257 1.13a11.042 11.042 0 005.516 5.516l1.13-2.257a1 1 0 011.21-.502l4.493 1.498a1 1 0 01.684.949V19a2 2 0 01-2 2h-1C9.716 21 3 14.284 3 6V5z"></path></svg>
                                    +62 896-9916-1526
                                </a>
                            </li>
                        </ul>
                    </div>
                </div>

                <!-- Sub-footer -->
                <div class="border-t border-border pt-8 flex flex-col md:flex-row items-center justify-between gap-6">
                    <!-- Copyright -->
                    <div class="text-sm text-text-muted text-center md:text-left">
                        &copy; {{ date('Y') }} P4I Publisher. All rights reserved.
                    </div>

                    <!-- Sub-footer Links -->
                    <div class="flex flex-wrap items-center justify-center gap-x-6 gap-y-2 text-sm text-text-secondary">
                        <a href="{{ route('about') }}" class="hover:text-primary transition-colors">Tentang P4I</a>
                        <a href="{{ route('contact') }}" class="hover:text-primary transition-colors">Kontak</a>
                    </div>
                </div>
            </div>
        </footer>

        <x-scroll-to-top />
    </body>
</html>
