<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Laporan Finansial & Transaksi') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">

            <!-- Filter Bar -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg mb-6 p-6">
                <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-col sm:flex-row gap-4 items-end">
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Mulai</label>
                        <input type="date" name="start_date" value="{{ $startDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-700">Tanggal Akhir</label>
                        <input type="date" name="end_date" value="{{ $endDate }}" class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-300 focus:ring focus:ring-indigo-200 focus:ring-opacity-50">
                    </div>
                    <div class="flex gap-2">
                        <button type="submit" class="bg-indigo-600 text-white px-4 py-2 rounded shadow hover:bg-indigo-700 transition">Terapkan Filter</button>
                        <a href="{{ route('admin.reports.export.orders', request()->all()) }}" class="bg-green-600 text-white px-4 py-2 rounded shadow hover:bg-green-700 transition">Ekspor Transaksi (CSV)</a>
                        <a href="{{ route('admin.reports.export.books', request()->all()) }}" class="bg-blue-600 text-white px-4 py-2 rounded shadow hover:bg-blue-700 transition">Ekspor Performa Buku (CSV)</a>
                    </div>
                </form>
            </div>

            <!-- Stats Cards Grid -->
            <div class="grid grid-cols-1 md:grid-cols-4 gap-6 mb-6">
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Pendapatan Bersih</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">Rp {{ number_format($totalRevenue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total Transaksi Sukses</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalTransactions) }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Rata-rata Nilai Pesanan</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">Rp {{ number_format($averageOrderValue, 0, ',', '.') }}</p>
                </div>
                <div class="bg-white p-6 rounded-lg shadow-sm border border-gray-100">
                    <h3 class="text-gray-500 text-sm font-medium uppercase tracking-wider">Total E-Book Terjual</h3>
                    <p class="text-3xl font-bold text-gray-900 mt-2">{{ number_format($totalBooksSold) }}</p>
                </div>
            </div>

            <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                <!-- Kolom Kiri: Tabel 5 E-Book Terlaris -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">5 E-Book Terlaris</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Judul</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Terjual</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Omzet</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($topBooks as $book)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">{{ $book->book->title ?? 'Deleted' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $book->total_sold }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">Rp {{ number_format($book->total_revenue, 0, ',', '.') }}</td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada data.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                </div>

                <!-- Kolom Kanan: Tabel 10 Transaksi Terbaru -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 border-b border-gray-200">
                        <h3 class="text-lg font-semibold text-gray-900">Transaksi Terbaru</h3>
                    </div>
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">ID / Waktu</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Pembeli</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($recentOrders as $order)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        <span class="font-medium text-gray-900">{{ $order->id }}</span><br>
                                        {{ $order->created_at->format('Y-m-d H:i') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">{{ $order->user->name ?? 'Unknown' }}</td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm">
                                        @if($order->status == 'success')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Success</span>
                                        @elseif($order->status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Failed</span>
                                        @endif
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="3" class="px-6 py-4 text-center text-sm text-gray-500">Tidak ada transaksi.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    @if($recentOrders->hasPages())
                    <div class="p-4 border-t border-gray-200">
                        {{ $recentOrders->links() }}
                    </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
