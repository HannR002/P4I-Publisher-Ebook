<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.payments.index') }}" class="text-text-muted hover:text-text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-text-primary">
                Detail Pembayaran: <span class="font-normal">{{ $paymentSubmission->order->order_number }}</span>
            </h2>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="flex flex-col lg:flex-row gap-6">
        <!-- Kolom Kiri: Info Pesanan & Item -->
        <div class="w-full lg:w-1/2 space-y-6">
            <div class="bg-surface rounded-xl border border-border p-6 md:p-8">
                <h3 class="text-lg font-bold text-text-primary mb-4 border-b border-border pb-2">Informasi Pesanan</h3>
                
                <div class="space-y-4 text-sm">
                    <div>
                        <span class="block text-text-muted">Nomor Pesanan</span>
                        <strong class="text-text-primary">{{ $paymentSubmission->order->order_number }}</strong>
                    </div>
                    <div>
                        <span class="block text-text-muted">Customer</span>
                        <strong class="text-text-primary">{{ $paymentSubmission->order->user->name }}</strong>
                        <span class="text-text-secondary">({{ $paymentSubmission->order->user->email }})</span>
                    </div>
                    <div>
                        <span class="block text-text-muted">Status Pesanan</span>
                        <strong class="text-text-primary uppercase">{{ $paymentSubmission->order->status }}</strong>
                    </div>
                    <div>
                        <span class="block text-text-muted">Metode Pembayaran</span>
                        <strong class="text-text-primary">{{ $paymentSubmission->paymentMethod->name }}</strong>
                    </div>
                </div>

                <h3 class="text-lg font-bold text-text-primary mt-8 mb-4 border-b border-border pb-2">Detail Item</h3>
                <ul class="space-y-3">
                    @foreach($paymentSubmission->order->items as $item)
                        <li class="flex justify-between items-center text-sm border-b border-border pb-2 last:border-0">
                            <div>
                                <span class="font-medium text-text-primary">{{ $item->title }}</span>
                                <span class="text-xs text-text-muted block">Format: {{ $item->format == 'digital' ? 'Digital E-Book/PDF' : 'Fisik Cetak' }}</span>
                            </div>
                            <span class="font-medium">Rp{{ number_format($item->price, 0, ',', '.') }}</span>
                        </li>
                    @endforeach
                </ul>

                <div class="mt-4 pt-4 border-t border-border flex justify-between items-center text-lg">
                    <strong class="text-text-primary">Total Tagihan:</strong>
                    <strong class="text-text-primary">Rp{{ number_format($paymentSubmission->order->total, 0, ',', '.') }}</strong>
                </div>
            </div>
        </div>

        <!-- Kolom Kanan: Bukti Pembayaran & Aksi -->
        <div class="w-full lg:w-1/2 space-y-6">
            <div class="bg-surface rounded-xl border border-border p-6 md:p-8">
                <div class="flex justify-between items-center mb-4 border-b border-border pb-2">
                    <h3 class="text-lg font-bold text-text-primary">Bukti Pembayaran</h3>
                    @php
                        $badgeVariant = match($paymentSubmission->status) {
                            'submitted' => 'warning',
                            'verified' => 'success',
                            'rejected' => 'error',
                            default => 'neutral'
                        };
                        $statusLabel = match($paymentSubmission->status) {
                            'submitted' => 'Menunggu Verifikasi',
                            'verified' => 'Terverifikasi',
                            'rejected' => 'Ditolak',
                            default => $paymentSubmission->status
                        };
                    @endphp
                    <x-badge variant="{{ $badgeVariant }}">{{ $statusLabel }}</x-badge>
                </div>
                
                <div class="mb-6">
                    <div class="flex justify-between items-center bg-background border border-border p-4 rounded-lg">
                        <div>
                            <span class="block text-sm text-text-muted mb-1">Nominal yang Disubmit User:</span>
                            <strong class="text-xl {{ $paymentSubmission->amount != $paymentSubmission->order->total ? 'text-red-600' : 'text-green-600' }}">
                                Rp{{ number_format($paymentSubmission->amount, 0, ',', '.') }}
                            </strong>
                        </div>
                    </div>
                    @if($paymentSubmission->amount != $paymentSubmission->order->total)
                        <div class="mt-2 p-3 bg-red-50 text-red-700 text-sm border border-red-200 rounded-lg flex gap-2 items-start">
                            <svg class="w-5 h-5 flex-shrink-0 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                            <p><strong>Peringatan Mismatch:</strong> Nominal bukti pembayaran berbeda dari total pesanan (Tagihan: Rp{{ number_format($paymentSubmission->order->total, 0, ',', '.') }}). Pastikan mengecek bukti transfer secara seksama sebelum menyetujui.</p>
                        </div>
                    @endif
                </div>

                <div class="mb-6 border border-border rounded-lg overflow-hidden bg-background">
                    <a href="{{ route('admin.payments.proof', $paymentSubmission->id) }}" target="_blank" class="block group relative">
                        <img src="{{ route('admin.payments.proof', $paymentSubmission->id) }}" alt="Bukti Pembayaran" class="w-full h-auto max-h-96 object-contain">
                        <div class="absolute inset-0 bg-black/50 opacity-0 group-hover:opacity-100 flex items-center justify-center transition-opacity">
                            <span class="text-white font-medium flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 21h7a2 2 0 002-2V9.414a1 1 0 00-.293-.707l-5.414-5.414A1 1 0 0012.586 3H7a2 2 0 00-2 2v11m0 5l4.879-4.879m0 0a3 3 0 104.243-4.242 3 3 0 00-4.243 4.242z"/></svg>
                                Buka Ukuran Penuh
                            </span>
                        </div>
                    </a>
                </div>
                
                <div class="text-sm text-text-secondary mb-6">
                    <p>Dikirim pada: {{ $paymentSubmission->submitted_at->format('d M Y, H:i') }}</p>
                    @if($paymentSubmission->verified_at)
                        <p>Diverifikasi pada: {{ $paymentSubmission->verified_at->format('d M Y, H:i') }} oleh {{ $paymentSubmission->verifier->name ?? 'Admin' }}</p>
                    @endif
                </div>

                @if($paymentSubmission->status === 'submitted')
                    <div class="border-t border-border pt-6" x-data="{ showReject: false }">
                        <h4 class="font-bold text-text-primary mb-4">Aksi Verifikasi</h4>
                        
                        <div class="flex gap-3">
                            <form action="{{ route('admin.payments.verify', $paymentSubmission->id) }}" method="POST" class="flex-1" onsubmit="return confirm('Apakah Anda yakin ingin memverifikasi pembayaran ini? Akses produk digital akan otomatis diberikan (jika ada).');">
                                @csrf
                                <button type="submit" class="w-full bg-green-600 hover:bg-green-700 text-white font-bold py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                    <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                    Verifikasi Pembayaran
                                </button>
                            </form>
                            
                            <button @click="showReject = !showReject" type="button" class="flex-1 bg-surface border border-red-200 text-red-600 hover:bg-red-50 font-bold py-2.5 px-4 rounded-lg transition-colors flex items-center justify-center gap-2">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                Tolak Pembayaran
                            </button>
                        </div>

                        <!-- Reject Form -->
                        <div x-show="showReject" x-collapse x-cloak class="mt-4">
                            <div class="p-4 border border-red-200 bg-red-50 rounded-lg">
                                <h5 class="font-bold text-red-800 text-sm mb-3">Konfirmasi Penolakan</h5>
                                <form action="{{ route('admin.payments.reject', $paymentSubmission->id) }}" method="POST">
                                    @csrf
                                    <textarea name="rejection_reason" rows="3" required placeholder="Tuliskan alasan penolakan (misal: Bukti transfer tidak valid/blur)..." class="w-full bg-white border border-red-300 rounded-lg p-3 text-sm focus:ring-red-500 focus:border-red-500 mb-3"></textarea>
                                    <button type="submit" class="w-full bg-red-600 hover:bg-red-700 text-white text-sm font-bold py-2 rounded-lg transition-colors">
                                        Kirim Penolakan
                                    </button>
                                </form>
                            </div>
                        </div>
                    </div>
                @endif
                
                @if($paymentSubmission->status === 'rejected')
                    <div class="border-t border-border pt-6 mt-6">
                        <div class="p-4 bg-red-50 text-red-800 border border-red-200 rounded-lg text-sm">
                            <strong class="block mb-1">Alasan Penolakan:</strong>
                            <p class="whitespace-pre-wrap">{{ $paymentSubmission->rejection_reason }}</p>
                        </div>
                    </div>
                @endif
                
            </div>
        </div>
    </div>
</x-admin-layout>
