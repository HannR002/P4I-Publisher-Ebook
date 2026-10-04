<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-text-primary">Verifikasi Pembayaran</h2>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <!-- Filters & Search -->
    <div class="mb-6 flex flex-col sm:flex-row justify-between items-center gap-4 bg-surface p-4 rounded-xl border border-border">
        <div class="flex flex-wrap gap-2 w-full sm:w-auto">
            @php
                $filters = [
                    'all' => 'Semua',
                    'submitted' => 'Menunggu Verifikasi',
                    'verified' => 'Terverifikasi',
                    'rejected' => 'Ditolak'
                ];
            @endphp
            @foreach($filters as $key => $label)
                <a href="{{ route('admin.payments.index', ['status' => $key, 'search' => $search]) }}" class="px-4 py-2 text-sm rounded-full transition-colors {{ $status == $key ? 'bg-primary text-white font-medium' : 'bg-background border border-border text-text-secondary hover:text-text-primary hover:bg-surface-hover' }}">
                    {{ $label }}
                </a>
            @endforeach
        </div>

        <form method="GET" action="{{ route('admin.payments.index') }}" class="w-full sm:w-80 flex">
            <input type="hidden" name="status" value="{{ $status }}">
            <input type="text" name="search" value="{{ $search }}" placeholder="Cari No Pesanan atau Pengguna..." class="w-full text-sm rounded-l-lg border border-border bg-background px-3 py-2 focus:ring-primary focus:border-primary">
            <button type="submit" class="bg-surface-hover border border-l-0 border-border px-3 rounded-r-lg text-text-secondary hover:text-text-primary">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"></path></svg>
            </button>
        </form>
    </div>

    <x-table-wrapper>
        <thead>
            <tr class="border-b border-border bg-surface-hover text-xs uppercase tracking-wider text-text-muted">
                <th class="px-4 py-3 text-left font-medium">No. Pesanan</th>
                <th class="px-4 py-3 text-left font-medium">Customer</th>
                <th class="px-4 py-3 text-left font-medium">Metode</th>
                <th class="px-4 py-3 text-left font-medium">Jumlah Tagihan</th>
                <th class="px-4 py-3 text-left font-medium">Status</th>
                <th class="px-4 py-3 text-left font-medium">Tanggal</th>
                <th class="px-4 py-3 text-right font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse($submissions as $submission)
                <tr class="hover:bg-surface-hover/30 transition-colors {{ $submission->status === 'submitted' ? 'bg-orange-50/10' : '' }}">
                    <td class="px-4 py-4">
                        <strong class="block text-sm font-bold text-text-primary">{{ $submission->order->order_number }}</strong>
                        <span class="text-xs text-text-muted">{{ str_replace('_', ' ', $submission->order->order_type) }}</span>
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-medium text-text-primary">{{ $submission->order->user->name }}</div>
                        <div class="text-xs text-text-muted">{{ $submission->order->user->email }}</div>
                    </td>
                    <td class="px-4 py-4 text-sm text-text-secondary">
                        {{ $submission->paymentMethod->name }}
                    </td>
                    <td class="px-4 py-4">
                        <div class="text-sm font-bold text-text-primary">Rp{{ number_format($submission->order->total, 0, ',', '.') }}</div>
                        @if($submission->amount != $submission->order->total)
                            <div class="text-xs text-red-500 font-bold mt-1 bg-red-50 inline-block px-1 rounded" title="Nominal yang disubmit: Rp{{ number_format($submission->amount, 0, ',', '.') }}">
                                Mismatch (Submit: Rp{{ number_format($submission->amount, 0, ',', '.') }})
                            </div>
                        @endif
                    </td>
                    <td class="px-4 py-4">
                        @php
                            $badgeVariant = match($submission->status) {
                                'submitted' => 'warning',
                                'verified' => 'success',
                                'rejected' => 'error',
                                default => 'neutral'
                            };
                            $statusLabel = match($submission->status) {
                                'submitted' => 'Menunggu Verifikasi',
                                'verified' => 'Terverifikasi',
                                'rejected' => 'Ditolak',
                                default => $submission->status
                            };
                        @endphp
                        <x-badge variant="{{ $badgeVariant }}">{{ $statusLabel }}</x-badge>
                    </td>
                    <td class="px-4 py-4 text-sm text-text-secondary whitespace-nowrap">
                        {{ $submission->submitted_at->format('d M Y, H:i') }}
                    </td>
                    <td class="px-4 py-4 text-right whitespace-nowrap">
                        <x-button type="button" href="{{ route('admin.payments.show', $submission->id) }}" variant="{{ $submission->status === 'submitted' ? 'primary' : 'secondary' }}" size="sm">
                            Detail & Aksi
                        </x-button>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="7" class="p-8 text-center text-text-muted">
                        Tidak ada pembayaran yang sesuai dengan kriteria.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table-wrapper>

    <div class="mt-5">
        {{ $submissions->links() }}
    </div>
</x-admin-layout>
