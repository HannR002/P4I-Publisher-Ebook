<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="transition-colors duration-300">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">

    <title>{{ config('app.name', 'P4I Publisher') }} - Pusat Penerbitan Buku</title>

    <!-- Fonts -->
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700&display=swap" rel="stylesheet" />

    <!-- Scripts -->
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <x-theme-init />
</head>
<body class="font-sans antialiased bg-background text-text-primary transition-colors duration-300" x-data="{ sidebarOpen: false }">
    <div class="min-h-screen flex flex-col">
        <!-- Top Navigation -->
        <nav class="bg-surface border-b border-border sticky top-0 z-50">
            <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
                <div class="flex justify-between h-16">
                    <div class="flex">
                        <!-- Logo -->
                        <div class="shrink-0 flex items-center gap-4">
                            <a href="{{ route('library.index') }}">
                                <x-application-logo class="block h-9 w-auto fill-current text-primary" />
                            </a>
                            <span class="text-xl font-bold tracking-tight text-text-primary border-l border-border pl-4">
                                Pusat Penerbitan Buku P4I
                            </span>
                        </div>

                        <!-- Sub Navigation Desktop -->
                        <div class="hidden space-x-8 sm:-my-px sm:ml-10 sm:flex">
                            <a href="{{ route('author.dashboard') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('author.dashboard') ? 'border-primary text-text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Dasbor
                            </a>
                            <a href="{{ route('author.submissions.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('author.submissions.*') && !request()->routeIs('author.submissions.create') ? 'border-primary text-text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Naskah Saya
                            </a>
                            <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('author.submissions.create') ? 'border-primary text-text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Ajukan Penerbitan
                            </a>
                            <a href="{{ route('books.index') }}" class="inline-flex items-center px-1 pt-1 border-b-2 {{ request()->routeIs('books.index') ? 'border-primary text-text-primary' : 'border-transparent text-text-secondary hover:text-text-primary hover:border-border' }} text-sm font-medium leading-5 transition duration-150 ease-in-out">
                                Buku Saya
                            </a>
                        </div>
                    </div>

                    <!-- Right Side Actions -->
                    <div class="hidden sm:flex sm:items-center sm:ml-6 gap-4">
                        <x-theme-switcher />

                        <!-- Switcher Mode Pembaca -->
                        <a href="{{ route('library.index') }}" class="text-sm text-primary hover:text-primary-hover bg-primary/10 px-3 py-1.5 rounded-md transition-colors flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                            Mode Pembaca
                        </a>

                        <!-- Author Badge -->
                        @if(Auth::user()->authorProfile && Auth::user()->authorProfile->isVerified())
                            <span class="inline-flex items-center rounded-md bg-green-500/10 px-2 py-1 text-xs font-medium text-green-600 dark:text-green-400 ring-1 ring-inset ring-green-500/20">Verified Author</span>
                        @endif

                        <!-- Settings Dropdown -->
                        <x-dropdown align="right" width="48">
                            <x-slot name="trigger">
                                <button class="inline-flex items-center px-3 py-2 border border-transparent text-sm leading-4 font-medium rounded-md text-text-secondary bg-surface hover:text-text-primary focus:outline-none transition ease-in-out duration-150">
                                    <div>{{ Auth::user()->authorProfile->pen_name ?? Auth::user()->name }}</div>
                                    <div class="ml-1">
                                        <svg class="fill-current h-4 w-4" xmlns="http://www.w3.org/2000/svg" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z" clip-rule="evenodd" />
                                        </svg>
                                    </div>
                                </button>
                            </x-slot>

                            <x-slot name="content">
                                <x-dropdown-link :href="route('profile.edit')">
                                    {{ __('Profil') }}
                                </x-dropdown-link>

                                <!-- Authentication -->
                                <form method="POST" action="{{ route('logout') }}">
                                    @csrf
                                    <x-dropdown-link :href="route('logout')"
                                            onclick="event.preventDefault();
                                                        this.closest('form').submit();">
                                        {{ __('Keluar') }}
                                    </x-dropdown-link>
                                </form>
                            </x-slot>
                        </x-dropdown>
                    </div>

                    <!-- Hamburger -->
                    <div class="-mr-2 flex items-center sm:hidden">
                        <button @click="sidebarOpen = ! sidebarOpen" class="inline-flex items-center justify-center p-2 rounded-md text-text-secondary hover:text-text-primary hover:bg-surface-hover focus:outline-none focus:bg-surface-hover focus:text-text-primary transition duration-150 ease-in-out">
                            <svg class="h-6 w-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                                <path :class="{'hidden': sidebarOpen, 'inline-flex': ! sidebarOpen }" class="inline-flex" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16" />
                                <path :class="{'hidden': ! sidebarOpen, 'inline-flex': sidebarOpen }" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                            </svg>
                        </button>
                    </div>
                </div>
            </div>

            <!-- Mobile Navigation Menu -->
            <div :class="{'block': sidebarOpen, 'hidden': ! sidebarOpen}" class="hidden sm:hidden bg-surface border-b border-border">
                <div class="pt-2 pb-3 space-y-1">
                    <x-responsive-nav-link :href="route('author.dashboard')" :active="request()->routeIs('author.dashboard')">
                        Dasbor
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('author.submissions.index')" :active="request()->routeIs('author.submissions.*') && !request()->routeIs('author.submissions.create')">
                        Naskah Saya
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('author.submissions.create')" :active="request()->routeIs('author.submissions.create')">
                        Ajukan Penerbitan
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('books.index')" :active="request()->routeIs('books.index')">
                        Buku Saya
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('library.index')">
                        Kembali ke Mode Pembaca
                    </x-responsive-nav-link>
                    <div class="border-t border-border pt-4 pb-1">
                        <div class="px-4">
                            <div class="font-medium text-base text-text-primary">{{ Auth::user()->name }}</div>
                            <div class="font-medium text-sm text-text-secondary">{{ Auth::user()->email }}</div>
                        </div>
                        <div class="mt-3 space-y-1">
                            <x-responsive-nav-link :href="route('profile.edit')">
                                {{ __('Profil') }}
                            </x-responsive-nav-link>
                            <form method="POST" action="{{ route('logout') }}">
                                @csrf
                                <x-responsive-nav-link :href="route('logout')"
                                        onclick="event.preventDefault();
                                                    this.closest('form').submit();">
                                    {{ __('Keluar') }}
                                </x-responsive-nav-link>
                            </form>
                        </div>
                    </div>
                </div>
            </div>
        </nav>

        <!-- Page Heading -->
        @isset($header)
            <header class="bg-surface border-b border-border shadow-sm">
                <div class="max-w-7xl mx-auto py-6 px-4 sm:px-6 lg:px-8">
                    {{ $header }}
                </div>
            </header>
        @endisset

        <!-- Main Content -->
        <main class="flex-1">
            <div class="max-w-7xl mx-auto py-10 px-4 sm:px-6 lg:px-8">
                <!-- Notifications -->
                @if (session('success'))
                    <div class="mb-6 bg-green-500/10 border-l-4 border-green-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-green-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-green-700 dark:text-green-300">
                                    {{ session('success') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                @if (session('warning'))
                    <div class="mb-6 bg-yellow-500/10 border-l-4 border-yellow-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-yellow-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-yellow-700 dark:text-yellow-300">
                                    {{ session('warning') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif
                @if (session('error'))
                    <div class="mb-6 bg-red-500/10 border-l-4 border-red-500 p-4 rounded-md">
                        <div class="flex">
                            <div class="flex-shrink-0">
                                <svg class="h-5 w-5 text-red-500" viewBox="0 0 20 20" fill="currentColor">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd" />
                                </svg>
                            </div>
                            <div class="ml-3">
                                <p class="text-sm text-red-700 dark:text-red-300">
                                    {{ session('error') }}
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                @yield('content')
                @isset($slot)
                    {{ $slot }}
                @endisset
            </div>
        </main>
    </div>
    <x-scroll-to-top />
</body>
</html>
