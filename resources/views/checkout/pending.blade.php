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
                <div class="absolute -top-24 -right-24 w-64 h-64 bg-amber-500/8 rounded-full blur-3xl pointer-events-none"></div>
                <div class="absolute -bottom-24 -left-24 w-64 h-64 bg-yellow-400/5 rounded-full blur-3xl pointer-events-none"></div>

                {{-- Clock Icon --}}
                <div class="relative z-10 flex justify-center mb-8">
                    <div class="w-24 h-24 bg-amber-500/10 border border-amber-500/20 rounded-full flex items-center justify-center" style="animation: tick 2s ease-in-out infinite;">
                        <svg class="w-12 h-12 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>

                {{-- Title --}}
                <div class="relative z-10 mb-6">
                    <p class="text-xs font-bold text-amber-500 uppercase tracking-[0.25em] mb-3">Transaksi Pending</p>
                    <h1 class="text-3xl font-black text-white tracking-tight leading-tight">
                        Menunggu Pembayaran
                    </h1>
                    <p class="text-gray-400 mt-3 leading-relaxed font-medium text-sm">
                        Pesanan Anda telah dibuat, namun pembayaran<br>belum kami terima. Segera selesaikan sebelum kadaluarsa.
                    </p>
                </div>

                {{-- Step-by-step instructions --}}
                <div class="relative z-10 bg-[#12141f] border border-[#2d3147] rounded-2xl p-5 mb-8 text-left space-y-3">
                    <p class="text-xs font-bold text-gray-400 uppercase tracking-widest mb-3">Langkah Selanjutnya</p>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 bg-amber-500/10 border border-amber-500/20 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                            <span class="text-[10px] font-black text-amber-400">1</span>
                        </div>
                        <p class="text-xs text-gray-400 leading-relaxed">Buka halaman Riwayat Transaksi Anda.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 bg-amber-500/10 border border-amber-500/20 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                            <span class="text-[10px] font-black text-amber-400">2</span>
                        </div>
                        <p class="text-xs text-gray-400 leading-relaxed">Klik tombol "Lanjutkan Pembayaran" di pesanan yang masih pending.</p>
                    </div>
                    <div class="flex items-start gap-3">
                        <div class="w-5 h-5 bg-amber-500/10 border border-amber-500/20 rounded-full flex items-center justify-center shrink-0 mt-0.5">
                            <span class="text-[10px] font-black text-amber-400">3</span>
                        </div>
                        <p class="text-xs text-gray-400 leading-relaxed">Selesaikan pembayaran lewat metode yang Anda pilih.</p>
                    </div>
                </div>

                {{-- Warning Notice --}}
                <div class="relative z-10 bg-amber-500/10 border border-amber-500/20 rounded-2xl px-5 py-4 mb-8 text-left">
                    <div class="flex gap-3">
                        <svg class="w-5 h-5 text-amber-400 shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <p class="text-xs text-amber-300 leading-relaxed font-medium">
                            Akses buku aktif otomatis setelah pembayaran dikonfirmasi. Pesanan yang tidak dibayar akan kadaluarsa dalam 24 jam.
                        </p>
                    </div>
                </div>

                {{-- CTA Buttons --}}
                <div class="relative z-10 flex flex-col gap-3">
                    <a
                        href="{{ route('my-orders') }}"
                        class="w-full inline-flex justify-center items-center gap-2.5 py-4 px-6 rounded-2xl text-sm font-extrabold text-white bg-gradient-to-r from-amber-600 to-amber-500 hover:from-amber-500 hover:to-amber-400 transition-all hover:-translate-y-0.5 shadow-lg shadow-amber-900/40"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/></svg>
                        Lihat Riwayat Transaksi
                    </a>
                    <a
                        href="{{ route('books.index') }}"
                        class="w-full inline-flex justify-center items-center gap-2 py-3 px-6 rounded-2xl text-sm font-semibold text-gray-400 hover:text-white hover:bg-white/5 transition-all border border-transparent hover:border-[#2d3147]"
                    >
                        Kembali ke Katalog
                    </a>
                </div>
            </div>

        </div>
    </div>

    <style>
        @keyframes tick {
            0%, 100% { transform: rotate(0deg); opacity: 1; }
            25% { transform: rotate(6deg); }
            75% { transform: rotate(-6deg); opacity: 0.85; }
        }
    </style>
</x-public-layout>
