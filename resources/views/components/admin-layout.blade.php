<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'P4I Digital Library') }} - Admin</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        
        <!-- Theme Initialization -->
        <x-theme-init />
    </head>
    <body class="font-sans antialiased bg-background text-text-primary transition-colors duration-300" x-data="{ sidebarOpen: false }">
        
        <!-- Mobile sidebar backdrop -->
        <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-300" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-300" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0" class="fixed inset-0 bg-gray-900/80 z-40 lg:hidden" @click="sidebarOpen = false"></div>

        <!-- Sidebar -->
        <div :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'" class="fixed inset-y-0 left-0 z-50 w-64 bg-surface border-r border-border transition-transform duration-300 ease-in-out lg:translate-x-0 lg:static lg:inset-0 lg:flex lg:w-64 lg:flex-col">
            <div class="flex h-16 shrink-0 items-center px-6 border-b border-border justify-between lg:justify-center">
                <a href="{{ route('admin.analytics.index') }}" class="flex items-center gap-2">
                    <svg class="h-8 w-8 text-primary" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2L2 7l10 5 10-5-10-5zM2 17l10 5 10-5M2 12l10 5 10-5"/></svg>
                    <span class="text-xl font-bold text-text-primary hidden lg:block">P4I Admin</span>
                </a>
                <button @click="sidebarOpen = false" class="lg:hidden text-text-muted hover:text-text-primary">
                    <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                </button>
            </div>
            
            <nav class="flex-1 overflow-y-auto px-4 py-4 space-y-1">
                <a href="{{ route('admin.analytics.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.analytics.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    <svg class="mr-3 h-5 w-5 {{ request()->routeIs('admin.analytics.*') ? 'text-primary' : 'text-text-muted group-hover:text-text-secondary' }}" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Dashboard
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-text-muted uppercase tracking-wider">Perpustakaan</p>
                </div>
                <a href="{{ route('admin.library.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.library.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    Semua Koleksi
                </a>
                
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-text-muted uppercase tracking-wider">Penerbitan</p>
                </div>
                <a href="{{ route('admin.submissions.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.submissions.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    Naskah Masuk
                </a>

                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-text-muted uppercase tracking-wider">Transaksi</p>
                </div>
                <a href="{{ route('admin.payments.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.payments.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    Verifikasi Pembayaran
                </a>
                <a href="{{ route('admin.payment-methods.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.payment-methods.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    Metode Pembayaran
                </a>
                
                <div class="pt-4 pb-2">
                    <p class="px-3 text-xs font-semibold text-text-muted uppercase tracking-wider">Sistem</p>
                </div>
                <a href="{{ route('admin.users.index') }}" class="group flex items-center px-3 py-2 text-sm font-medium rounded-md {{ request()->routeIs('admin.users.*') ? 'bg-primary/10 text-primary' : 'text-text-secondary hover:bg-surface-hover hover:text-text-primary' }}">
                    Pengguna
                </a>

                <div class="pt-4 mt-auto border-t border-border">
                    <a href="https://p4ijournal.org" class="group flex items-center px-3 py-2 text-xs font-medium rounded-md text-text-muted hover:text-primary hover:bg-surface-hover">
                        <svg class="mr-2 h-4 w-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path></svg>
                        Situs Utama P4I
                    </a>
                </div>
            </nav>
        </div>

        <!-- Main Content -->
        <div class="flex flex-1 flex-col lg:pl-64 min-h-screen">
            <div class="sticky top-0 z-10 flex h-16 shrink-0 items-center gap-x-4 border-b border-border bg-surface px-4 shadow-sm sm:gap-x-6 sm:px-6 lg:px-8">
                <button type="button" class="-m-2.5 p-2.5 text-text-muted lg:hidden" @click="sidebarOpen = true">
                    <span class="sr-only">Open sidebar</span>
                    <svg class="h-6 w-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                
                <div class="flex flex-1 gap-x-4 self-stretch lg:gap-x-6">
                    <div class="flex flex-1 items-center">
                        <!-- Breadcrumb or Search placeholder -->
                    </div>
                    <div class="flex items-center gap-x-4 lg:gap-x-6">
                        <x-theme-switcher />
                        
                        <!-- Profile dropdown -->
                        <div class="relative" x-data="{ open: false }">
                            <button @click="open = !open" type="button" class="-m-1.5 flex items-center p-1.5">
                                <span class="sr-only">Open user menu</span>
                                <div class="h-8 w-8 rounded-full bg-primary/20 flex items-center justify-center text-primary font-bold">
                                    {{ substr(Auth::user()->name, 0, 1) }}
                                </div>
                                <span class="hidden lg:flex lg:items-center">
                                    <span class="ml-4 text-sm font-semibold leading-6 text-text-primary">{{ Auth::user()->name }}</span>
                                    <svg class="ml-2 h-5 w-5 text-text-muted" viewBox="0 0 20 20" fill="currentColor"><path fill-rule="evenodd" d="M5.23 7.21a.75.75 0 011.06.02L10 11.168l3.71-3.938a.75.75 0 111.08 1.04l-4.25 4.5a.75.75 0 01-1.08 0l-4.25-4.5a.75.75 0 01.02-1.06z" clip-rule="evenodd" /></svg>
                                </span>
                            </button>
                            
                            <div x-show="open" @click.away="open = false" x-transition class="absolute right-0 z-10 mt-2.5 w-32 origin-top-right rounded-md bg-surface py-2 shadow-lg ring-1 ring-border focus:outline-none">
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <button type="submit" class="block w-full px-3 py-1 text-sm leading-6 text-text-primary hover:bg-surface-hover text-left">Sign out</button>
                                </form>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

            <main class="flex-1 py-10">
                <div class="px-4 sm:px-6 lg:px-8 max-w-7xl mx-auto">
                    {{ $slot }}
                </div>
            </main>
        </div>
        <x-scroll-to-top />
    </body>
</html>
