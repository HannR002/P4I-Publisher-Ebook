<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.users.index') }}" class="text-text-muted hover:text-text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-text-primary">
                Detail Pengguna: <span class="font-normal">{{ $user->name }}</span>
            </h2>
        </div>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="space-y-6">
        <!-- Profil Card -->
        <div class="bg-surface rounded-xl border border-border p-6 md:p-8 flex flex-col lg:flex-row gap-8 justify-between items-center shadow-sm">
            <div class="flex items-center gap-6">
                <div class="h-20 w-20 rounded-full bg-primary/10 flex items-center justify-center border-4 border-background shadow-md">
                    <span class="text-3xl font-bold text-primary">{{ substr($user->name, 0, 1) }}</span>
                </div>
                <div>
                    <h3 class="text-2xl font-black text-text-primary">{{ $user->name }}</h3>
                    <p class="text-text-secondary">{{ $user->email }}</p>
                    <div class="mt-3 flex flex-wrap gap-2">
                        @if ($user->is_admin)
                            <x-badge variant="neutral">Admin</x-badge>
                        @endif
                        @if ($user->is_author)
                            <x-badge variant="success">Author</x-badge>
                        @endif
                        @if ($user->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                        @else
                            <x-badge variant="error">Ditangguhkan</x-badge>
                        @endif
                        <span class="text-xs text-text-muted flex items-center ml-2 border-l border-border pl-2">
                            Bergabung: {{ $user->created_at->format('d M Y') }}
                        </span>
                    </div>
                </div>
            </div>

            <div class="flex gap-4 w-full lg:w-auto">
                <div class="flex-1 lg:flex-none text-center p-4 bg-background border border-border rounded-xl">
                    <span class="block text-2xl font-black text-primary">Rp{{ number_format((float)$user->total_spent, 0, ',', '.') }}</span>
                    <span class="block text-xs font-bold text-text-muted uppercase mt-1">Total Belanja</span>
                </div>
                <div class="flex-1 lg:flex-none text-center p-4 bg-background border border-border rounded-xl">
                    <span class="block text-2xl font-black text-text-primary">{{ (int)$user->completed_orders_count }}</span>
                    <span class="block text-xs font-bold text-text-muted uppercase mt-1">Pesanan Sukses</span>
                </div>
            </div>
        </div>

        <div class="grid grid-cols-1 xl:grid-cols-2 gap-6">
            <!-- Koleksi Lisensi -->
            <div class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm flex flex-col h-[500px]">
                <div class="p-5 border-b border-border bg-background">
                    <h3 class="text-lg font-bold text-text-primary flex items-center justify-between">
                        Koleksi Lisensi Digital
                        <x-badge variant="primary">{{ (int)$user->active_licenses_count }} Aktif</x-badge>
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1">
                    <ul class="divide-y divide-border">
                        @forelse ($user->bookLicenses as $license)
                            <li class="p-5 hover:bg-surface-hover/30 transition-colors {{ $license->status !== 'active' ? 'bg-red-50/50' : '' }}" x-data="{ showModal: false }">
                                <div class="flex justify-between items-start gap-4">
                                    <div>
                                        <h4 class="font-bold text-text-primary mb-1">{{ $license->book->title ?? '[Buku Dihapus]' }}</h4>
                                        <div class="text-xs text-text-muted mb-2">Diberikan pada: {{ $license->created_at->format('d M Y, H:i') }}</div>

                                        @if ($license->status !== 'active')
                                            <div class="mt-2 text-xs font-medium text-red-600 bg-red-50 border border-red-200 p-2 rounded w-full line-clamp-2" title="{{ $license->revocation_reason }}">
                                                Dicabut: {{ $license->revocation_reason }}
                                            </div>
                                        @endif
                                    </div>
                                    <div class="flex flex-col items-end gap-2 shrink-0">
                                        @if ($license->status === 'active')
                                            <x-badge variant="success">Aktif</x-badge>
                                            <button @click="showModal = true" class="text-xs font-bold text-red-600 hover:text-red-800 transition-colors">Cabut Lisensi</button>

                                            <!-- Revoke Modal -->
                                            <div x-show="showModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/50">
                                                <div @click.outside="showModal = false" class="bg-surface rounded-xl border border-border shadow-2xl w-full max-w-md overflow-hidden">
                                                    <div class="p-5 border-b border-border bg-background">
                                                        <h3 class="font-bold text-lg text-text-primary">Cabut Akses Digital</h3>
                                                    </div>
                                                    <form action="{{ route('admin.users.licenses.revoke', [$user->id, $license->id]) }}" method="POST">
                                                        @csrf
                                                        @method('PATCH')
                                                        <div class="p-5 space-y-4">
                                                            <div>
                                                                <span class="block text-xs font-bold text-text-muted mb-1">Publikasi</span>
                                                                <span class="block text-sm font-medium text-text-primary">{{ $license->book->title ?? 'N/A' }}</span>
                                                            </div>
                                                            <div>
                                                                <label class="block text-sm font-bold text-text-primary mb-2">Alasan Pencabutan</label>
                                                                <textarea name="reason" rows="3" required class="w-full rounded-lg border-border bg-background px-3 py-2 text-sm focus:ring-red-500 focus:border-red-500 transition-colors" placeholder="Tuliskan alasan..."></textarea>
                                                            </div>
                                                        </div>
                                                        <div class="p-4 border-t border-border bg-background flex justify-end gap-3">
                                                            <x-button type="button" @click="showModal = false" variant="secondary">Batal</x-button>
                                                            <button type="submit" class="bg-red-600 hover:bg-red-700 text-white font-bold py-2 px-4 rounded-lg transition-colors">Cabut Akses</button>
                                                        </div>
                                                    </form>
                                                </div>
                                            </div>
                                        @else
                                            <x-badge variant="error">Dicabut</x-badge>
                                            <form action="{{ route('admin.users.licenses.restore', [$user->id, $license->id]) }}" method="POST" class="inline" onsubmit="return confirm('Kembalikan akses membaca untuk publikasi ini?');">
                                                @csrf
                                                @method('PATCH')
                                                <button type="submit" class="text-xs font-bold text-green-600 hover:text-green-800 transition-colors">Pulihkan Akses</button>
                                            </form>
                                        @endif
                                    </div>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center text-text-muted flex flex-col items-center justify-center h-full">
                                <svg class="w-12 h-12 mb-3 text-text-muted/50" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/></svg>
                                Belum ada lisensi / akses digital yang dimiliki pengguna ini.
                            </li>
                        @endforelse
                    </ul>
                </div>
            </div>

            <!-- Riwayat Pesanan -->
            <div class="bg-surface rounded-xl border border-border overflow-hidden shadow-sm flex flex-col h-[500px]">
                <div class="p-5 border-b border-border bg-background">
                    <h3 class="text-lg font-bold text-text-primary">Riwayat Transaksi Pesanan</h3>
                </div>
                <div class="overflow-y-auto flex-1 p-0">
                    <table class="w-full text-left text-sm">
                        <thead class="sticky top-0 bg-surface-hover/90 backdrop-blur z-10 border-b border-border text-xs uppercase text-text-muted font-bold tracking-wider">
                            <tr>
                                <th class="px-5 py-3">ID / Tanggal</th>
                                <th class="px-5 py-3">Item</th>
                                <th class="px-5 py-3 text-right">Tagihan</th>
                                <th class="px-5 py-3 text-right">Status</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-border">
                            @forelse ($user->orders as $order)
                                <tr class="hover:bg-surface-hover/30 transition-colors">
                                    <td class="px-5 py-4 whitespace-nowrap">
                                        <div class="font-bold text-text-primary">#{{ $order->id }}</div>
                                        <div class="text-xs text-text-muted mt-1">{{ $order->created_at->format('d M Y, H:i') }}</div>
                                    </td>
                                    <td class="px-5 py-4">
                                        <ul class="space-y-1">
                                            @foreach ($order->items as $item)
                                                <li class="text-text-secondary line-clamp-1 text-xs before:content-['•'] before:mr-1 before:text-primary">
                                                    {{ $item->book->title ?? '[Dihapus]' }}
                                                </li>
                                            @endforeach
                                        </ul>
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right font-bold text-text-primary">
                                        Rp{{ number_format((float)$order->gross_amount, 0, ',', '.') }}
                                    </td>
                                    <td class="px-5 py-4 whitespace-nowrap text-right">
                                        @if ($order->status === 'success')
                                            <x-badge variant="success">Berhasil</x-badge>
                                        @elseif ($order->status === 'pending')
                                            <x-badge variant="warning">Pending</x-badge>
                                        @else
                                            <x-badge variant="neutral">{{ ucfirst($order->status) }}</x-badge>
                                        @endif
                                    </td>
                                </tr>
                            @empty
                                <tr>
                                    <td colspan="4" class="p-12 text-center text-text-muted">
                                        Belum ada riwayat transaksi.
                                    </td>
                                </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-admin-layout>
