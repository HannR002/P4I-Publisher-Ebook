<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">

        <title>{{ config('app.name', 'P4I Publisher') }} - Authentication</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        <!-- Scripts -->
        @vite(['resources/css/app.css', 'resources/js/app.js'])
        <!-- Theme Initialization (Prevents FOUC) -->
        <x-theme-init />
    </head>
    <body class="font-sans text-text-primary antialiased bg-background transition-colors duration-300 min-h-screen p-4 md:p-6 lg:p-8">

        <!-- Global Asymmetric Bento Grid Layout -->
        <div class="grid grid-cols-1 lg:grid-cols-12 gap-4 lg:gap-6 min-h-[calc(100vh-4rem)]">

            <!-- LEFT: Authentication Form (40%) -->
            <div class="lg:col-span-5 flex flex-col justify-center bg-surface border border-border rounded-[2rem] p-8 lg:p-12 shadow-[0_8px_30px_rgb(0,0,0,0.04)] relative overflow-hidden">
                <!-- Branding / Logo -->
                <div class="absolute top-8 left-8">
                    <a href="/" class="flex items-center gap-2">
                        <svg class="w-8 h-8 text-blue-600" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path></svg>
                        <span class="font-extrabold text-xl tracking-wider text-gray-900">P4I<span class="text-blue-600"> E-Book</span></span>
                    </a>
                </div>

                <div class="w-full max-w-md mx-auto mt-12 z-10 relative">
                    {{ $slot }}
                </div>

                <!-- Subtle geometric decoration -->
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-blue-500/5 rounded-full blur-3xl pointer-events-none"></div>
            </div>

            <!-- RIGHT: Dynamic Visual Area (60%) -->
            <div class="hidden lg:flex lg:col-span-7 rounded-[2rem] relative overflow-hidden bg-gradient-to-br from-blue-50 via-white to-emerald-50 border border-gray-100 shadow-lg">
                <!-- Mesh Gradient Orbs -->
                <div class="absolute top-[-10%] left-[-10%] w-[50%] h-[50%] bg-blue-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob"></div>
                <div class="absolute top-[20%] right-[-10%] w-[60%] h-[60%] bg-emerald-400 rounded-full mix-blend-multiply filter blur-[100px] opacity-10 animate-blob animation-delay-2000"></div>
                <div class="absolute bottom-[-20%] left-[20%] w-[70%] h-[70%] bg-blue-300 rounded-full mix-blend-multiply filter blur-[120px] opacity-10 animate-blob animation-delay-4000"></div>

                <!-- Frosted Glass Content Box -->
                <div class="absolute inset-x-8 bottom-8 top-auto p-8 rounded-3xl bg-white/40 backdrop-blur-xl border border-white/60 shadow-xl">
                    <div class="flex flex-col gap-4">
                        <div class="inline-block px-4 py-1.5 rounded-full bg-blue-50 text-blue-600 text-xs font-bold uppercase tracking-widest w-max border border-blue-100">
                            ✨ Katalog Publikasi Digital
                        </div>
                        <h2 class="text-3xl font-extrabold text-gray-900 leading-tight">
                            "Membaca adalah melawan, menulis adalah mencipta."
                        </h2>
                        <p class="text-gray-600 text-sm mt-2 font-medium max-w-xl leading-relaxed">
                            Jelajahi ribuan koleksi literatur terbaik, dukung penulis lokal, dan nikmati bacaan digital dengan teknologi perlindungan DRM kelas dunia.
                        </p>

                        <!-- Simulated Bento Bookshelf / Carousel blocks -->
                        <div class="grid grid-cols-3 gap-4 mt-6">
                            <div class="h-32 rounded-2xl bg-gradient-to-t from-gray-900/80 to-transparent border border-white/20 flex items-end p-4 relative overflow-hidden group cursor-pointer shadow-md transition-transform hover:-translate-y-1">
                                <div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:opacity-70 group-hover:scale-110 transition-all duration-500" style="background-image: url('https://images.unsplash.com/photo-1544947950-fa07a98d237f?q=80&w=400&auto=format&fit=crop');"></div>
                                <span class="text-xs font-bold relative z-10 text-white tracking-wide">Fiksi Modern</span>
                            </div>
                            <div class="h-32 rounded-2xl bg-gradient-to-t from-gray-900/80 to-transparent border border-white/20 flex items-end p-4 relative overflow-hidden group cursor-pointer shadow-md transition-transform hover:-translate-y-1">
                                <div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:opacity-70 group-hover:scale-110 transition-all duration-500" style="background-image: url('https://images.unsplash.com/photo-1589829085413-56de8ae18c73?q=80&w=400&auto=format&fit=crop');"></div>
                                <span class="text-xs font-bold relative z-10 text-white tracking-wide">Sains & Tekno</span>
                            </div>
                            <div class="h-32 rounded-2xl bg-gradient-to-t from-gray-900/80 to-transparent border border-white/20 flex items-end p-4 relative overflow-hidden group cursor-pointer shadow-md transition-transform hover:-translate-y-1">
                                <div class="absolute inset-0 bg-cover bg-center opacity-40 group-hover:opacity-70 group-hover:scale-110 transition-all duration-500" style="background-image: url('https://images.unsplash.com/photo-1512820790803-83ca734da794?q=80&w=400&auto=format&fit=crop');"></div>
                                <span class="text-xs font-bold relative z-10 text-white tracking-wide">Pengembangan Diri</span>
                            </div>
                        </div>
                    </div>
                </div>
            </div>

        </div>

        <style>
            @keyframes blob {
                0% { transform: translate(0px, 0px) scale(1); }
                33% { transform: translate(30px, -50px) scale(1.1); }
                66% { transform: translate(-20px, 20px) scale(0.9); }
                100% { transform: translate(0px, 0px) scale(1); }
            }
            .animate-blob {
                animation: blob 7s infinite alternate ease-in-out;
            }
            .animation-delay-2000 {
                animation-delay: 2s;
            }
            .animation-delay-4000 {
                animation-delay: 4s;
            }
        </style>
    </body>
</html>
