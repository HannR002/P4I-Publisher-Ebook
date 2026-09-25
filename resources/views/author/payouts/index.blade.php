<x-author-layout>
    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            <div class="flex items-center justify-between">
                <div>
                    <h2 class="text-2xl font-bold text-gray-900 tracking-tight">Dompet & Royalti</h2>
                    <p class="text-sm text-gray-500 mt-1">Kelola dan tarik saldo pendapatan dari buku Anda.</p>
                </div>
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

            <!-- Balance Cards -->
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <!-- Available Balance -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 bg-indigo-50 text-indigo-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Saldo Tersedia</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($availableBalance, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Pending Balance -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 bg-yellow-50 text-yellow-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Dalam Proses Penarikan</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($pendingBalance, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Withdrawn Balance -->
                <div class="bg-white rounded-xl shadow-sm border border-gray-100 p-6 flex items-center space-x-4">
                    <div class="p-3 bg-green-50 text-green-600 rounded-lg">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    </div>
                    <div>
                        <p class="text-sm font-medium text-gray-500">Total Telah Ditarik</p>
                        <p class="text-2xl font-bold text-gray-900">Rp {{ number_format($withdrawnBalance, 0, ',', '.') }}</p>
                    </div>
                </div>
            </div>

            <!-- Withdrawal Action -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Tarik Saldo</h3>
                    <p class="text-sm text-gray-500 mt-1">Minimal penarikan adalah Rp 100.000. Dana akan ditransfer ke rekening bank Anda yang terdaftar.</p>
                </div>
                <div class="p-6">
                    @if(empty($author->bank_name) || empty($author->bank_account) || empty($author->bank_holder_name))
                        <div class="bg-yellow-50 border border-yellow-200 text-yellow-800 rounded-lg p-4 flex items-start space-x-3">
                            <svg class="w-5 h-5 text-yellow-600 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                            <div>
                                <p class="text-sm font-medium">Informasi Rekening Belum Lengkap</p>
                                <p class="text-sm mt-1">Anda harus melengkapi data rekening bank pada profil sebelum dapat melakukan penarikan dana.</p>
                            </div>
                        </div>
                    @else
                        <div class="bg-gray-50 p-4 rounded-lg mb-6 border border-gray-200">
                            <h4 class="text-sm font-semibold text-gray-700 mb-2">Informasi Rekening Tujuan</h4>
                            <div class="grid grid-cols-2 gap-4 text-sm">
                                <div>
                                    <span class="text-gray-500 block">Bank:</span>
                                    <span class="font-medium text-gray-900">{{ $author->bank_name }}</span>
                                </div>
                                <div>
                                    <span class="text-gray-500 block">Atas Nama:</span>
                                    <span class="font-medium text-gray-900">{{ $author->bank_holder_name }}</span>
                                </div>
                                <div class="col-span-2">
                                    <span class="text-gray-500 block">Nomor Rekening:</span>
                                    <span class="font-medium text-gray-900">{{ $author->bank_account }}</span>
                                </div>
                            </div>
                        </div>

                        <form action="{{ route('author.payouts.store') }}" method="POST" class="flex flex-col sm:flex-row items-end gap-4" onsubmit="return confirm('Apakah Anda yakin ingin menarik dana sebesar Rp ' + document.getElementById('amount').value + '?')">
                            @csrf
                            <div class="flex-1 w-full">
                                <label for="amount" class="block text-sm font-medium text-gray-700 mb-1">Nominal Penarikan</label>
                                <div class="flex rounded-md shadow-sm">
                                    <span class="inline-flex items-center rounded-l-md border border-r-0 border-gray-300 bg-gray-50 px-3 text-gray-500 sm:text-sm">Rp</span>
                                    <input type="number" name="amount" id="amount" min="100000" max="{{ $availableBalance }}" step="1" required class="block w-full min-w-0 flex-1 rounded-none rounded-r-md border-gray-300 px-3 py-2 focus:border-indigo-500 focus:ring-indigo-500 sm:text-sm" placeholder="100000">
                                </div>
                            </div>
                            <button type="submit" @if($availableBalance < 100000) disabled @endif class="w-full sm:w-auto flex-shrink-0 inline-flex justify-center items-center px-6 py-2 bg-indigo-600 hover:bg-indigo-700 border border-transparent rounded-md font-semibold text-sm text-white focus:outline-none transition ease-in-out duration-150 disabled:opacity-50">
                                Ajukan Penarikan
                            </button>
                        </form>
                    @endif
                </div>
            </div>

            <!-- Payout History -->
            <div class="bg-white rounded-xl shadow-sm border border-gray-100 overflow-hidden">
                <div class="p-6 border-b border-gray-100">
                    <h3 class="text-lg font-semibold text-gray-900">Riwayat Penarikan Dana</h3>
                </div>
                
                @if($payouts->isEmpty())
                    <div class="p-8 text-center text-gray-500 text-sm">
                        Belum ada riwayat penarikan dana.
                    </div>
                @else
                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Tanggal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Nominal</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Status</th>
                                    <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase tracking-wider">Catatan / Bukti</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @foreach($payouts as $payout)
                                    <tr>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-900">
                                            {{ $payout->created_at->format('d M Y H:i') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap text-sm font-medium text-gray-900">
                                            Rp {{ number_format($payout->amount, 0, ',', '.') }}
                                        </td>
                                        <td class="px-6 py-4 whitespace-nowrap">
                                            @if($payout->status === 'requested')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-blue-100 text-blue-800">Diminta</span>
                                            @elseif($payout->status === 'processing')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Diproses</span>
                                            @elseif($payout->status === 'completed')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Selesai</span>
                                            @elseif($payout->status === 'rejected')
                                                <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Ditolak</span>
                                            @endif
                                        </td>
                                        <td class="px-6 py-4 text-sm text-gray-500">
                                            @if($payout->status === 'completed' && $payout->reference_number)
                                                <div class="text-xs">Ref: {{ $payout->reference_number }}</div>
                                            @endif
                                            @if($payout->status === 'rejected' && $payout->admin_notes)
                                                <div class="text-xs text-red-600">Alasan: {{ $payout->admin_notes }}</div>
                                            @endif
                                        </td>
                                    </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                    @if($payouts->hasPages())
                        <div class="p-4 border-t border-gray-100">
                            {{ $payouts->links() }}
                        </div>
                    @endif
                @endif
            </div>

        </div>
    </div>
</x-author-layout>
