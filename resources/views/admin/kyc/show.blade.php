<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Detail Verifikasi KYC') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900 grid grid-cols-1 md:grid-cols-2 gap-8">

                    <div>
                        <h3 class="text-lg font-bold border-b pb-2 mb-4">Informasi Penulis</h3>
                        <table class="w-full text-sm">
                            <tr><td class="py-2 font-semibold text-gray-600">Nama Pena</td><td class="py-2">{{ $author->pen_name }}</td></tr>
                            <tr><td class="py-2 font-semibold text-gray-600">Email Akun</td><td class="py-2">{{ $author->user->email }}</td></tr>
                            <tr><td class="py-2 font-semibold text-gray-600">NIK KTP</td><td class="py-2 font-mono">{{ $author->id_card_number }}</td></tr>
                            <tr><td class="py-2 font-semibold text-gray-600">Nama Rekening</td><td class="py-2">{{ $author->bank_holder_name }}</td></tr>
                            <tr><td class="py-2 font-semibold text-gray-600">Bank & No Rek</td><td class="py-2">{{ $author->bank_name }} - <span class="font-mono">{{ $author->bank_account }}</span></td></tr>
                            <tr><td class="py-2 font-semibold text-gray-600">Status KYC</td>
                                <td class="py-2">
                                    <span class="px-2 py-1 text-xs rounded-full
                                        {{ $author->kyc_status == 'verified' ? 'bg-green-100 text-green-800' : ($author->kyc_status == 'rejected' ? 'bg-red-100 text-red-800' : 'bg-yellow-100 text-yellow-800') }}">
                                        {{ strtoupper($author->kyc_status) }}
                                    </span>
                                </td>
                            </tr>
                        </table>

                        @if($author->kyc_status == 'pending')
                            <div class="mt-8 pt-4 border-t border-gray-200">
                                <h4 class="font-bold mb-4">Tindakan Keputusan</h4>

                                <form action="{{ route('admin.kyc.approve', $author->id) }}" method="POST" class="inline-block" onsubmit="return confirm('Apakah Anda yakin menyetujui KYC penulis ini?');">
                                    @csrf @method('PATCH')
                                    <button type="submit" class="bg-green-600 text-white px-4 py-2 rounded-md hover:bg-green-700 text-sm font-semibold">Setujui (Approve)</button>
                                </form>

                                <div class="mt-6 p-4 bg-red-50 rounded-lg border border-red-100" x-data="{ open: false }">
                                    <button @click="open = !open" type="button" class="text-red-600 text-sm font-bold underline">Tolak Pengajuan (Reject)</button>

                                    <form action="{{ route('admin.kyc.reject', $author->id) }}" method="POST" x-show="open" class="mt-4">
                                        @csrf @method('PATCH')
                                        <label class="block text-sm text-gray-700 mb-1">Alasan Penolakan</label>
                                        <textarea name="rejection_reason" required rows="3" class="w-full border-gray-300 rounded-md shadow-sm mb-2" placeholder="Contoh: KTP buram..."></textarea>
                                        <button type="submit" class="bg-red-600 text-white px-4 py-1.5 rounded-md hover:bg-red-700 text-sm">Kirim Penolakan</button>
                                    </form>
                                </div>
                            </div>
                        @endif
                    </div>

                    <div>
                        <h3 class="text-lg font-bold border-b pb-2 mb-4">Dokumen KTP</h3>
                        <div class="border rounded-lg bg-gray-50 overflow-hidden h-64 flex items-center justify-center">
                            @if(Str::endsWith($author->id_card_path, '.pdf'))
                                <a href="{{ route('admin.kyc.stream-id-card', $author->id) }}" target="_blank" class="text-indigo-600 underline text-sm">Buka Dokumen PDF KTP</a>
                            @else
                                <a href="{{ route('admin.kyc.stream-id-card', $author->id) }}" target="_blank">
                                    <img src="{{ route('admin.kyc.stream-id-card', $author->id) }}" class="max-h-full max-w-full object-contain" alt="KTP">
                                </a>
                            @endif
                        </div>
                        <p class="text-xs text-gray-500 mt-2 text-center">Klik pada area dokumen untuk melihat ukuran penuh pada tab baru.</p>
                    </div>

                </div>
            </div>
        </div>
    </div>
</x-app-layout>
