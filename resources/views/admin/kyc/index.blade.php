<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Verifikasi KYC Penulis') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">

                    @if (session('success'))
                        <div class="mb-4 bg-green-50 border-l-4 border-green-400 p-4 rounded-md">
                            <p class="text-sm text-green-700">{{ session('success') }}</p>
                        </div>
                    @endif

                    <div class="mb-4 flex gap-4">
                        <a href="{{ route('admin.kyc.index', ['status' => 'all']) }}" class="px-3 py-1 text-sm rounded-full {{ $status == 'all' ? 'bg-gray-800 text-white' : 'bg-gray-200 text-gray-800' }}">Semua</a>
                        <a href="{{ route('admin.kyc.index', ['status' => 'pending']) }}" class="px-3 py-1 text-sm rounded-full {{ $status == 'pending' ? 'bg-yellow-500 text-white' : 'bg-gray-200 text-gray-800' }}">Menunggu Verifikasi</a>
                        <a href="{{ route('admin.kyc.index', ['status' => 'verified']) }}" class="px-3 py-1 text-sm rounded-full {{ $status == 'verified' ? 'bg-green-500 text-white' : 'bg-gray-200 text-gray-800' }}">Terverifikasi</a>
                        <a href="{{ route('admin.kyc.index', ['status' => 'rejected']) }}" class="px-3 py-1 text-sm rounded-full {{ $status == 'rejected' ? 'bg-red-500 text-white' : 'bg-gray-200 text-gray-800' }}">Ditolak</a>
                    </div>

                    <div class="overflow-x-auto">
                        <table class="min-w-full divide-y divide-gray-200">
                            <thead class="bg-gray-50">
                                <tr>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Penulis</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">NIK</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Rekening</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Status</th>
                                    <th class="px-6 py-3 text-left text-xs font-medium text-gray-500 uppercase">Tanggal Daftar</th>
                                    <th class="px-6 py-3 text-right text-xs font-medium text-gray-500 uppercase">Aksi</th>
                                </tr>
                            </thead>
                            <tbody class="bg-white divide-y divide-gray-200">
                                @forelse($authors as $author)
                                <tr>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        <div class="text-sm font-medium text-gray-900">{{ $author->pen_name }}</div>
                                        <div class="text-sm text-gray-500">{{ $author->user->email }}</div>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $author->id_card_number }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $author->bank_name }} - {{ $author->bank_account }}<br>
                                        <small>{{ $author->bank_holder_name }}</small>
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap">
                                        @if($author->kyc_status == 'pending')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-yellow-100 text-yellow-800">Pending</span>
                                        @elseif($author->kyc_status == 'verified')
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-green-100 text-green-800">Verified</span>
                                        @else
                                            <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full bg-red-100 text-red-800">Rejected</span>
                                        @endif
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500">
                                        {{ $author->created_at->format('d M Y') }}
                                    </td>
                                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                        <a href="{{ route('admin.kyc.show', $author->id) }}" class="text-indigo-600 hover:text-indigo-900">Periksa</a>
                                    </td>
                                </tr>
                                @empty
                                <tr>
                                    <td colspan="6" class="px-6 py-4 text-center text-sm text-gray-500">Belum ada pengajuan.</td>
                                </tr>
                                @endforelse
                            </tbody>
                        </table>
                    </div>
                    <div class="mt-4">
                        {{ $authors->links() }}
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
