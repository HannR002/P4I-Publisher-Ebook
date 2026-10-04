<x-public-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 py-12">
        <x-breadcrumb :links="['Beranda' => url('/'), 'Pesanan Saya' => route('dashboard'), 'Pembayaran' => null]" class="mb-8" />

        <div class="bg-surface border border-border rounded-2xl shadow-sm overflow-hidden">
            <!-- Order Header -->
            <div class="border-b border-border p-6 sm:p-8 bg-gray-50 dark:bg-gray-800/50">
                <div class="flex flex-col sm:flex-row justify-between items-start sm:items-center gap-4">
                    <div>
                        <div class="text-text-muted font-medium mb-1">Pesanan #{{ $order->order_number }}</div>
                        <h1 class="text-2xl font-bold text-text-primary">
                            @if($order->order_type === 'digital')
                                Akses Digital
                            @elseif($order->order_type === 'physical')
                                Buku Cetak Fisik
                            @else
                                {{ ucfirst($order->order_type) }}
                            @endif
                        </h1>
                    </div>

                    @php
                        $statusMap = [
                            'awaiting_payment' => ['label' => 'Menunggu Pembayaran', 'color' => 'yellow'],
                            'payment_submitted' => ['label' => 'Bukti Pembayaran Dikirim', 'color' => 'blue'],
                            'verified' => ['label' => 'Terverifikasi', 'color' => 'green'],
                            'rejected' => ['label' => 'Ditolak', 'color' => 'red'],
                            'processing' => ['label' => 'Sedang Diproses', 'color' => 'indigo'],
                            'completed' => ['label' => 'Selesai', 'color' => 'green'],
                        ];
                        $statusInfo = $statusMap[$order->status] ?? ['label' => str($order->status)->replace('_', ' ')->title(), 'color' => 'gray'];
                    @endphp

                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-{{ $statusInfo['color'] }}-100 text-{{ $statusInfo['color'] }}-800 dark:bg-{{ $statusInfo['color'] }}-900/30 dark:text-{{ $statusInfo['color'] }}-300">
                        {{ $statusInfo['label'] }}
                    </span>
                </div>
            </div>

            <div class="p-6 sm:p-8">
                <!-- Order Items Summary -->
                <div class="mb-10">
                    <h2 class="text-lg font-bold text-text-primary mb-4">Rincian Pesanan</h2>
                    <div class="space-y-4 border border-border rounded-xl p-4">
                        @foreach($order->items as $item)
                            <div class="flex justify-between items-center text-text-secondary">
                                <span>{{ $item->description }}</span>
                                <strong class="text-text-primary font-medium">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</strong>
                            </div>
                        @endforeach

                        @if($order->shipping_cost > 0)
                            <div class="flex justify-between items-center text-text-secondary">
                                <span>Biaya Pengiriman</span>
                                <strong class="text-text-primary font-medium">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</strong>
                            </div>
                        @endif

                        <div class="pt-4 mt-2 border-t border-border flex justify-between items-center text-xl">
                            <strong class="text-text-primary">Total Tagihan</strong>
                            <strong class="text-primary font-bold">Rp {{ number_format($order->total, 0, ',', '.') }}</strong>
                        </div>
                    </div>
                </div>

                @if(session('success'))
                    <x-alert type="success" class="mb-8">
                        {{ session('success') }}
                    </x-alert>
                @endif

                @if(session('error'))
                    <x-alert type="error" class="mb-8">
                        {{ session('error') }}
                    </x-alert>
                @endif

                <!-- Payment Rejected Notice -->
                @if($order->status === 'rejected')
                    <div class="mb-10 p-5 bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-xl">
                        <div class="flex gap-3">
                            <svg class="w-6 h-6 text-red-600 dark:text-red-400 shrink-0" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" /></svg>
                            <div>
                                <h3 class="font-bold text-red-800 dark:text-red-300">Pembayaran Ditolak</h3>
                                <p class="text-red-700 dark:text-red-400 mt-1">
                                    Alasan Penolakan: <strong>{{ $order->paymentSubmissions->last()?->notes ?: 'Tidak ada alasan spesifik yang diberikan admin.' }}</strong>
                                </p>
                                <p class="text-red-700 dark:text-red-400 mt-2 text-sm">
                                    Silakan periksa kembali nominal atau bukti pembayaran Anda, lalu kirimkan ulang bukti yang valid di bawah ini.
                                </p>
                            </div>
                        </div>
                    </div>
                @endif

                <!-- Verified/Completed Digital Purchase -->
                @if($order->status === 'verified' || $order->status === 'completed')
                    @if($order->order_type === 'digital')
                        <div class="mb-8 p-6 bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-xl text-center">
                            <svg class="w-16 h-16 text-green-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                            <h2 class="text-xl font-bold text-green-800 dark:text-green-300 mb-2">Pembayaran Telah Diverifikasi</h2>
                            <p class="text-green-700 dark:text-green-400 mb-6">Pembayaran Anda telah diterima. Publikasi digital sekarang sudah dapat diakses melalui perpustakaan.</p>

                            <div class="flex justify-center gap-4">
                                <!-- As we don't have direct library item link in order currently without joining, user can go to dashboard -->
                                <x-button tag="a" :href="route('dashboard')" color="primary">
                                    Lihat Koleksi Saya
                                </x-button>
                            </div>
                        </div>
                    @else
                        <!-- Physical Order State -->
                        <div class="mb-8 p-6 bg-indigo-50 dark:bg-indigo-900/20 border border-indigo-200 dark:border-indigo-800 rounded-xl">
                            <h2 class="text-xl font-bold text-indigo-800 dark:text-indigo-300 mb-2">Pesanan Fisik Sedang Diproses</h2>
                            <p class="text-indigo-700 dark:text-indigo-400">Pembayaran telah diverifikasi. Admin sedang memproses pesanan fisik Anda untuk pengiriman.</p>
                            <p class="text-sm text-indigo-600 dark:text-indigo-400 mt-3 italic">* Catatan: Sistem pelacakan pengiriman otomatis sedang dalam pengembangan. Mohon tunggu konfirmasi admin selanjutnya melalui email/kontak Anda.</p>
                        </div>
                    @endif
                @elseif($order->status === 'payment_submitted')
                    <div class="mb-8 p-6 bg-blue-50 dark:bg-blue-900/20 border border-blue-200 dark:border-blue-800 rounded-xl text-center">
                        <svg class="w-16 h-16 text-blue-500 mx-auto mb-4" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" /></svg>
                        <h2 class="text-xl font-bold text-blue-800 dark:text-blue-300 mb-2">Bukti Pembayaran Dikirim</h2>
                        <p class="text-blue-700 dark:text-blue-400">Terima kasih. Kami telah menerima bukti pembayaran Anda. Admin akan memverifikasi pesanan ini secara manual dalam waktu 1x24 jam kerja.</p>
                    </div>
                @endif

                <!-- Payment Methods & Upload Form -->
                @if(in_array($order->status, ['awaiting_payment', 'rejected']))
                    <div class="mb-8 pt-6 border-t border-border">
                        <h2 class="text-xl font-bold text-text-primary mb-6">Pilih Metode Pembayaran</h2>

                        @if($paymentMethods->isEmpty())
                            <x-empty-state icon="credit-card" title="Tidak ada metode" description="Metode pembayaran aktif belum dikonfigurasi oleh admin." class="bg-gray-50 dark:bg-gray-800/50" />
                        @else
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-10">
                                @foreach($paymentMethods as $method)
                                    <div class="border border-border bg-gray-50 dark:bg-gray-800/50 rounded-xl p-5 hover:border-primary transition-colors">
                                        <h3 class="font-bold text-lg text-text-primary mb-1">{{ $method->name }}</h3>
                                        <p class="text-sm text-text-muted mb-4">{{ $method->account_name }}</p>

                                        @if($method->account_number)
                                            <div x-data="{ copied: false }" class="bg-background border border-border p-3 rounded-lg flex justify-between items-center mb-4">
                                                <code class="text-text-primary font-mono text-base tracking-wide">{{ $method->account_number }}</code>
                                                <button type="button"
                                                        @click="navigator.clipboard.writeText('{{ $method->account_number }}'); copied = true; setTimeout(() => copied = false, 2000)"
                                                        class="text-primary hover:text-primary-focus text-sm font-medium px-3 py-1 rounded bg-primary/10">
                                                    <span x-text="copied ? 'Tersalin!' : 'Salin Nomor'"></span>
                                                </button>
                                            </div>
                                        @endif

                                        @if($method->qr_image_path)
                                            <div class="mt-4 text-center bg-white p-3 rounded-lg inline-block border border-gray-200">
                                                <img src="{{ Storage::disk('public')->url($method->qr_image_path) }}" alt="QR resmi {{ $method->name }}" class="h-32 object-contain">
                                            </div>
                                        @endif

                                        @if($method->instructions)
                                            <div class="mt-4 text-sm text-text-secondary">
                                                {!! nl2br(e($method->instructions)) !!}
                                            </div>
                                        @endif
                                    </div>
                                @endforeach
                            </div>

                            <!-- Upload Form -->
                            <div class="bg-gray-50 dark:bg-gray-800/50 rounded-xl p-6 sm:p-8 border border-border">
                                <h3 class="text-xl font-bold text-text-primary mb-6">Upload Bukti Pembayaran</h3>

                                <form method="POST" enctype="multipart/form-data" action="{{ route('manual-orders.proof', $order) }}" class="space-y-6">
                                    @csrf

                                    <div>
                                        <x-input-label for="payment_method_id" value="Transfer ke Bank/E-Wallet" />
                                        <select id="payment_method_id" name="payment_method_id" required class="mt-1 block w-full rounded-md border-gray-300 dark:border-gray-700 dark:bg-gray-900 dark:text-gray-300 focus:border-violet-500 focus:ring-violet-500 shadow-sm">
                                            <option value="">-- Pilih metode yang Anda gunakan --</option>
                                            @foreach($paymentMethods as $method)
                                                <option value="{{ $method->id }}">{{ $method->name }}</option>
                                            @endforeach
                                        </select>
                                        <x-input-error :messages="$errors->get('payment_method_id')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="amount" value="Nominal yang Ditransfer" />
                                        <div class="relative mt-1">
                                            <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                                <span class="text-gray-500 sm:text-sm">Rp</span>
                                            </div>
                                            <x-text-input id="amount" type="number" name="amount" value="{{ old('amount', $order->total) }}" required class="pl-10 block w-full" />
                                        </div>
                                        <p class="text-xs text-text-muted mt-1">Harus sesuai dengan Total Tagihan.</p>
                                        <x-input-error :messages="$errors->get('amount')" class="mt-2" />
                                    </div>

                                    <div>
                                        <x-input-label for="proof" value="File Bukti Transfer (Image / PDF)" />
                                        <input type="file" id="proof" name="proof" accept=".jpg,.jpeg,.png,.pdf" required class="mt-1 block w-full text-sm text-text-secondary
                                          file:mr-4 file:py-2 file:px-4
                                          file:rounded-full file:border-0
                                          file:text-sm file:font-semibold
                                          file:bg-primary/10 file:text-primary
                                          hover:file:bg-primary/20 cursor-pointer" />
                                        <x-input-error :messages="$errors->get('proof')" class="mt-2" />
                                    </div>

                                    <div class="pt-4">
                                        <x-button type="submit" color="primary" class="w-full justify-center !py-3 text-base">
                                            {{ $order->status === 'rejected' ? 'Kirim Bukti Pembayaran Baru' : 'Kirim Bukti Pembayaran' }}
                                        </x-button>
                                    </div>
                                </form>
                            </div>
                        @endif
                    </div>
                @endif

            </div>
        </div>
    </div>
</x-public-layout>
