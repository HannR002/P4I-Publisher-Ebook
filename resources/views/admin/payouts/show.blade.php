<x-app-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">

            <div class="flex justify-between items-center">
                <div>
                    <h2 class="text-2xl font-bold text-gray-800">Detail Penarikan Dana</h2>
                    <p class="text-sm text-gray-500 mt-1">ID Pengajuan: {{ $payout->id }}</p>
                </div>
                <a href="{{ route('admin.payouts.index') }}" class="text-sm text-indigo-600 hover:text-indigo-900 font-medium">
                    &larr; Kembali ke Daftar
                </a>
            </div>

            @if(session('success'))
                <div class="bg-green-50 border-l-4 border-green-500 p-4 rounded-md">
                    <p class="text-sm text-green-700">{{ session('success') }}</p>
                </div>
            @endif
            @if($errors->any())
                <div class="bg-red-50 border-l-4 border-red-500 p-4 rounded-md">
                    <ul class="text-sm text-red-700 list-disc list-inside">
                        @foreach($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Info Penulis & Status -->
                <div class="md:col-span-2 space-y-6">
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Informasi Pembayaran</h3>

                        <div class="grid grid-cols-2 gap-y-4 gap-x-6 text-sm">
                            <div>
                                <span class="text-gray-500 block mb-1">Penulis:</span>
                                <span class="font-medium text-gray-900">{{ $payout->author->pen_name }} ({{ $payout->author->user->email }})</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Nominal Ditarik:</span>
                                <span class="font-bold text-xl text-gray-900">Rp {{ number_format($payout->amount, 0, ',', '.') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Tanggal Pengajuan:</span>
                                <span class="font-medium text-gray-900">{{ $payout->created_at->format('d F Y H:i') }}</span>
                            </div>
                            <div>
                                <span class="text-gray-500 block mb-1">Status:</span>
                                @if($payout->status === 'requested')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Diminta</span>
                                @elseif($payout->status === 'processing')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                                @elseif($payout->status === 'completed')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                @elseif($payout->status === 'rejected')
                                    <span class="px-2 py-1 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                @endif
                            </div>
                        </div>

                        <div class="mt-6 p-4 bg-gray-50 rounded-lg border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-800 mb-3">Tujuan Transfer (Snapshot)</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Bank:</span>
                                    <span class="font-medium text-gray-900">{{ $payout->bank_name_snapshot }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Nomor Rekening:</span>
                                    <span class="font-medium text-gray-900 font-mono">{{ $payout->bank_account_snapshot }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-500 block">Atas Nama:</span>
                                    <span class="font-medium text-gray-900">{{ $payout->bank_holder_name_snapshot }}</span>
                                </div>
                            </div>
                        </div>
                    </div>

                    <!-- Buku Besar Terkait -->
                    <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                        <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Rincian Buku Besar (Ledger) Pendapatan</h3>

                        <div class="overflow-x-auto">
                            <table class="min-w-full divide-y divide-gray-200">
                                <thead class="bg-gray-50">
                                    <tr>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500">Tanggal</th>
                                        <th scope="col" class="px-4 py-2 text-left text-xs font-medium text-gray-500">Buku</th>
                                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500">Penjualan</th>
                                        <th scope="col" class="px-4 py-2 text-right text-xs font-medium text-gray-500">Pendapatan Penulis</th>
                                    </tr>
                                </thead>
                                <tbody class="bg-white divide-y divide-gray-200">
                                    @foreach($ledgers as $ledger)
                                        <tr>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-gray-500">
                                                {{ $ledger->created_at->format('d/m/Y') }}
                                            </td>
                                            <td class="px-4 py-3 text-xs text-gray-900 font-medium">
                                                {{ $ledger->book->title }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-right text-gray-500">
                                                Rp {{ number_format($ledger->gross_sale, 0, ',', '.') }}
                                            </td>
                                            <td class="px-4 py-3 whitespace-nowrap text-xs text-right font-medium text-green-600">
                                                Rp {{ number_format($ledger->author_earning, 0, ',', '.') }}
                                            </td>
                                        </tr>
                                    @endforeach
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>

                <!-- Aksi Proses -->
                <div class="space-y-6">
                    @if(in_array($payout->status, ['requested', 'processing']))
                        <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-lg font-semibold text-gray-900 mb-4 border-b pb-2">Aksi Verifikasi</h3>

                            <form action="{{ route('admin.payouts.complete', $payout) }}" method="POST" enctype="multipart/form-data" class="space-y-4 mb-6" onsubmit="return confirm('Selesaikan penarikan dana ini?')">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Nomor Referensi Transfer</label>
                                    <input type="text" name="reference_number" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm">
                                </div>
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Bukti Transfer (Opsional)</label>
                                    <input type="file" name="transfer_proof" accept="image/jpeg,image/png,application/pdf" class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100">
                                </div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-green-600 hover:bg-green-700">
                                    Tandai Selesai & Ditransfer
                                </button>
                            </form>

                            <hr>

                            <form action="{{ route('admin.payouts.reject', $payout) }}" method="POST" class="space-y-4 mt-6" onsubmit="return confirm('Tolak penarikan dana ini dan kembalikan saldo ke penulis?')">
                                @csrf
                                <div>
                                    <label class="block text-sm font-medium text-gray-700">Alasan Penolakan</label>
                                    <textarea name="admin_notes" rows="3" required class="mt-1 block w-full rounded-md border-gray-300 shadow-sm focus:border-red-500 focus:ring-red-500 sm:text-sm" placeholder="Contoh: Nomor rekening tidak valid"></textarea>
                                </div>
                                <button type="submit" class="w-full flex justify-center py-2 px-4 border border-transparent rounded-md shadow-sm text-sm font-medium text-white bg-red-600 hover:bg-red-700">
                                    Tolak Pencairan Dana
                                </button>
                            </form>
                        </div>
                    @endif

                    @if($payout->processed_by)
                        <div class="bg-gray-50 border border-gray-200 shadow-sm sm:rounded-lg p-6">
                            <h3 class="text-sm font-semibold text-gray-700 mb-3 border-b border-gray-200 pb-2">Riwayat Pemrosesan</h3>
                            <div class="space-y-2 text-sm text-gray-600">
                                <p><span class="font-medium text-gray-900">Diproses Oleh:</span> {{ $payout->processor->name }}</p>
                                <p><span class="font-medium text-gray-900">Tanggal:</span> {{ $payout->processed_at->format('d/m/Y H:i') }}</p>

                                @if($payout->reference_number)
                                    <p><span class="font-medium text-gray-900">No. Ref:</span> {{ $payout->reference_number }}</p>
                                @endif

                                @if($payout->admin_notes)
                                    <div class="mt-2 p-3 bg-red-50 border-l-4 border-red-500 text-red-700 rounded text-xs">
                                        <strong>Catatan Penolakan:</strong><br>
                                        {{ $payout->admin_notes }}
                                    </div>
                                @endif
                            </div>
                        </div>
                    @endif
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
