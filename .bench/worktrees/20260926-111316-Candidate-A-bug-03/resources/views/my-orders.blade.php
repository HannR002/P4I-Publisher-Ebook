<x-public-layout>
    <div class="max-w-5xl mx-auto px-4 sm:px-6 lg:px-8 py-14">

        {{-- Page Header --}}
        <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4 mb-12">
            <div>
                <p class="text-sm font-bold text-blue-600 dark:text-indigo-400 uppercase tracking-widest mb-2">Portal Pembaca</p>
                <h1 class="text-4xl font-black text-gray-900 dark:text-white tracking-tight">Riwayat Transaksi</h1>
                <p class="text-gray-500 dark:text-gray-400 mt-2 font-medium">Semua pesanan yang pernah Anda buat.</p>
            </div>
            <a href="{{ route('my-library') }}" class="inline-flex items-center gap-1.5 text-xs font-bold text-blue-700 bg-blue-50 hover:bg-blue-100 dark:text-indigo-300 dark:bg-indigo-900/30 dark:hover:bg-indigo-900/50 transition-colors px-3 py-1.5 rounded-full border border-blue-200 dark:border-indigo-800 shrink-0">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                Perpustakaan Saya
            </a>
        </div>

        @if($orders->isEmpty())
            {{-- ─── EMPTY STATE ─────────────────────────────────────────────── --}}
            <div class="flex flex-col items-center justify-center py-28 text-center">
                <div class="w-24 h-24 bg-gray-100 dark:bg-[#1a1d2e] rounded-3xl flex items-center justify-center mb-6 shadow-inner">
                    <svg class="w-12 h-12 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                    </svg>
                </div>
                <h2 class="text-2xl font-extrabold text-gray-900 dark:text-white mb-2">Belum Ada Transaksi</h2>
                <p class="text-gray-500 dark:text-gray-400 max-w-sm mb-8 leading-relaxed">Riwayat pesanan akan muncul di sini setelah Anda melakukan pembelian pertama.</p>
                <a href="{{ route('books.index') }}" class="inline-flex items-center gap-2 px-8 py-4 rounded-full text-base font-bold text-white bg-black dark:bg-indigo-600 hover:bg-gray-800 dark:hover:bg-indigo-500 transition-all hover:-translate-y-0.5 hover:shadow-xl">
                    Telusuri Katalog
                </a>
            </div>
        @else
            {{-- ─── ORDER LIST ──────────────────────────────────────────────── --}}
            <div class="space-y-4">
                @foreach($orders as $order)
                    <div class="bg-white dark:bg-[#1a1d2e] border border-gray-100 dark:border-[#2d3147] rounded-2xl shadow-sm hover:shadow-md transition-shadow overflow-hidden">
                        {{-- Order Header --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-b border-gray-100 dark:border-[#2d3147] bg-gray-50/60 dark:bg-[#12141f]">
                            <div class="flex items-center gap-4 min-w-0">
                                {{-- Status Badge --}}
                                @php
                                    $badgeConfig = match($order->status) {
                                        'success'  => ['bg' => 'bg-emerald-100 dark:bg-emerald-900/30', 'text' => 'text-emerald-700 dark:text-emerald-400', 'dot' => 'bg-emerald-500', 'label' => 'Berhasil'],
                                        'pending'  => ['bg' => 'bg-yellow-100 dark:bg-yellow-900/30',  'text' => 'text-yellow-700 dark:text-yellow-400',  'dot' => 'bg-yellow-500',  'label' => 'Menunggu'],
                                        'failed'   => ['bg' => 'bg-red-100 dark:bg-red-900/30',     'text' => 'text-red-700 dark:text-red-400',     'dot' => 'bg-red-500',     'label' => 'Gagal'],
                                        'expired'  => ['bg' => 'bg-gray-100 dark:bg-gray-800',    'text' => 'text-gray-600 dark:text-gray-400',    'dot' => 'bg-gray-400',    'label' => 'Kadaluarsa'],
                                        default    => ['bg' => 'bg-gray-100 dark:bg-gray-800',    'text' => 'text-gray-600 dark:text-gray-400',    'dot' => 'bg-gray-400',    'label' => ucfirst($order->status)],
                                    };
                                @endphp
                                <span class="inline-flex items-center gap-2 px-3 py-1 rounded-full text-xs font-bold {{ $badgeConfig['bg'] }} {{ $badgeConfig['text'] }} shrink-0">
                                    <span class="w-1.5 h-1.5 rounded-full {{ $badgeConfig['dot'] }}"></span>
                                    {{ $badgeConfig['label'] }}
                                </span>
                                <div class="min-w-0">
                                    <p class="text-xs font-mono text-gray-400 dark:text-gray-500 truncate">{{ $order->id }}</p>
                                </div>
                            </div>
                            <div class="flex items-center gap-4 sm:shrink-0">
                                <p class="text-xs text-gray-400 font-medium">{{ $order->created_at->format('d M Y, H:i') }}</p>
                                @if($order->payment_type)
                                    <span class="text-xs font-semibold text-gray-500 dark:text-gray-400 bg-gray-100 dark:bg-[#2d3147] px-2 py-0.5 rounded-full">{{ str_replace('_', ' ', $order->payment_type) }}</span>
                                @endif
                            </div>
                        </div>

                        {{-- Order Items --}}
                        <div class="px-6 py-4">
                            @foreach($order->items as $item)
                                <div class="flex items-center gap-3 {{ !$loop->last ? 'mb-3' : '' }}">
                                    {{-- Book Cover Thumbnail --}}
                                    <div class="w-9 h-12 rounded-md overflow-hidden bg-gray-100 dark:bg-gray-800 border border-gray-200 dark:border-gray-700 shrink-0">
                                        @if($item->book && $item->book->cover_image_path)
                                            <img src="{{ $item->book->cover_image_path }}" alt="" class="w-full h-full object-cover">
                                        @else
                                            <div class="w-full h-full flex items-center justify-center">
                                                <svg class="w-4 h-4 text-gray-300 dark:text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex-grow min-w-0">
                                        <p class="text-sm font-semibold text-gray-900 dark:text-white truncate">{{ $item->book->title ?? '(Buku Tidak Tersedia)' }}</p>
                                        @if($item->book)
                                            <p class="text-xs text-gray-400">{{ $item->book->author }}</p>
                                        @endif
                                    </div>
                                    <p class="text-sm font-bold text-gray-700 dark:text-gray-300 shrink-0">Rp {{ number_format($item->price, 0, ',', '.') }}</p>
                                </div>
                            @endforeach
                        </div>

                        {{-- Order Footer --}}
                        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-3 px-6 py-4 border-t border-gray-100 dark:border-[#2d3147] bg-gray-50/40 dark:bg-[#1a1d2e]">
                            {{-- Total --}}
                            <div>
                                <span class="text-xs text-gray-400 font-medium">TOTAL</span>
                                <p class="text-lg font-black text-gray-900 dark:text-white">Rp {{ number_format($order->gross_amount, 0, ',', '.') }}</p>
                            </div>

                            {{-- Action Buttons --}}
                            <div class="flex items-center gap-2">
                                @if($order->status === 'success')
                                    <a href="{{ route('my-library') }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-emerald-700 dark:text-emerald-400 bg-emerald-50 dark:bg-emerald-900/20 hover:bg-emerald-100 dark:hover:bg-emerald-900/40 border border-emerald-200 dark:border-emerald-800 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2.5" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"/></svg>
                                        Buka Perpustakaan
                                    </a>
                                @elseif($order->status === 'pending' && $order->snap_token)
                                    <button
                                        onclick="triggerPayment('{{ $order->snap_token }}', '{{ $order->id }}')"
                                        data-testid="btn-pay-snap-{{ $order->id }}"
                                        class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-500 hover:to-purple-500 transition-all shadow-sm"
                                    >
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 10h18M7 15h1m4 0h1m-7 4h12a3 3 0 003-3V8a3 3 0 00-3-3H6a3 3 0 00-3 3v8a3 3 0 003 3z"/></svg>
                                        Lanjutkan Pembayaran
                                    </button>
                                @elseif($order->status === 'pending')
                                    <a href="{{ route('checkout.show', $order->id) }}" class="inline-flex items-center gap-1.5 px-4 py-2 rounded-xl text-xs font-bold text-yellow-700 dark:text-yellow-400 bg-yellow-50 dark:bg-yellow-900/20 hover:bg-yellow-100 dark:hover:bg-yellow-900/40 border border-yellow-200 dark:border-yellow-800 transition-colors">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                        Lihat Tagihan
                                    </a>
                                @endif
                            </div>
                        </div>
                    </div>
                @endforeach
            </div>

            {{-- Pagination --}}
            @if($orders->hasPages())
                <div class="mt-10 pt-6 border-t border-gray-200 dark:border-[#2d3147]">
                    {{ $orders->links() }}
                </div>
            @endif
        @endif
    </div>

    {{-- Midtrans Snap.js --}}
    @if($orders->where('status', 'pending')->where('snap_token', '!=', null)->isNotEmpty())
        @if(config('midtrans.is_production'))
            <script src="https://app.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @else
            <script src="https://app.sandbox.midtrans.com/snap/snap.js" data-client-key="{{ config('midtrans.client_key') }}"></script>
        @endif
        <script>
            function triggerPayment(snapToken, orderId) {
                snap.pay(snapToken, {
                    onSuccess: function(result) {
                        window.location.href = "{{ route('checkout.success') }}?order_id=" + orderId;
                    },
                    onPending: function(result) {
                        window.location.reload();
                    },
                    onError: function(result) {
                        alert('Terjadi kesalahan. Silakan coba lagi.');
                    },
                    onClose: function() { /* user tutup popup */ }
                });
            }
        </script>
    @endif
</x-public-layout>
