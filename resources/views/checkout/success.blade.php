<x-public-layout>
    {{-- ─── DARK PREMIUM FULL-PAGE OVERRIDE ──────────────────────────────────── --}}
    <style>
        body { background-color: #0f1117 !important; }
    </style>

    <div class="min-h-[calc(100vh-64px)] flex items-center justify-center px-4 py-16 bg-[#0f1117]">
        <div class="w-full max-w-lg">

            {{-- Glassmorphism Card --}}
            <div class="bg-[#1a1d2e] border border-[#2d3147] rounded-[2.5rem] p-12 text-center relative overflow-hidden shadow-[0_40px_80px_rgba(0,0,0,0.5)]">

                {{-- Ambient Glow --}}
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-emerald-500/10 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-emerald-400/5 rounded-full blur-3xl pointer-events-none"></div>

                {{-- ✓ CheckCircle Icon --}}
                <div class="relative z-10 flex justify-center mb-8">
                    <div class="w-24 h-24 bg-emerald-500/10 border border-emerald-500/20 rounded-full flex items-center justify-center animate-pulse-slow">
                        <svg class="w-12 h-12 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Title --}}
                <div class="relative z-10 mb-6">
                    <p class="text-xs font-bold text-emerald-500 uppercase tracking-[0.25em] mb-3">Transaksi Sukses</p>
                    <h1 class="text-3xl font-black text-white tracking-tight leading-tight">
                        Pembayaran Berhasil!
                    </h1>
                    <p class="text-gray-400 mt-3 leading-relaxed font-medium text-sm">
                        Selamat! Pembelian Anda telah dikonfirmasi.<br>Buku kini tersedia di perpustakaan Anda.
                    </p>
                </div>

                {{-- Order Reference --}}
                @if($order)
                    <div class="relative z-10 mb-8 bg-[#12141f] border border-[#2d3147] rounded-2xl px-5 py-3 inline-flex items-center gap-3">
                        <svg class="w-4 h-4 text-gray-500 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        <div class="text-left">
                            <p class="text-[10px] text-gray-500 uppercase tracking-widest font-bold">Order ID</p>
                            <p class="text-xs font-mono text-indigo-400 mt-0.5">{{ $order->id }}</p>
                        </div>
                    </div>
                @endif

                {{-- Info Notice --}}
                <div class="relative z-10 bg-emerald-500/10 border border-emerald-500/20 rounded-2xl px-5 py-4 mb-8 text-left">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-emerald-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                        <p class="text-xs text-emerald-300 leading-relaxed font-medium">
                            Status buku akan aktif secara otomatis setelah konfirmasi webhook diterima dari server Midtrans — biasanya dalam beberapa detik.
                        </p>
                    </div>
                </div>

                {{-- CTA Button --}}
                <div class="relative z-10 flex flex-col gap-3">
                    <a
                        href="{{ route('my-library') }}"
                        data-testid="btn-to-library"
                        class="w-full inline-flex justify-center items-center gap-2.5 py-4 px-6 rounded-2xl text-sm font-extrabold text-white bg-gradient-to-r from-emerald-600 to-emerald-500 hover:from-emerald-500 hover:to-emerald-400 transition-all hover:-translate-y-0.5 shadow-lg shadow-emerald-900/40"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                        Buka Perpustakaan Saya
                    </a>
                    <a
                        href="{{ route('my-orders') }}"
                        class="w-full inline-flex justify-center items-center gap-2 py-3 px-6 rounded-2xl text-sm font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition-all border border-transparent hover:border-[#2d3147]"
                    >
                        Lihat Riwayat Transaksi
                    </a>
                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes pulse-slow {
            0%, 100% { opacity: 1; transform: scale(1); }
            50% { opacity: 0.85; transform: scale(1.04); }
        }
        .animate-pulse-slow { animation: pulse-slow 3s ease-in-out infinite; }
    </style>
</x-public-layout>
