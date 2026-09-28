<x-public-layout><div class="max-w-2xl mx-auto px-6 py-12"><div class="bg-white dark:bg-[#1a1d2e] rounded-3xl border dark:border-gray-700 p-8"><h1 class="text-3xl font-black dark:text-white">Pusat Penerbitan Buku</h1><p class="mt-2 text-gray-500">Buat profil penulis untuk mengajukan naskah buku digital atau cetak.</p><form action="{{ url('/author/register') }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-5">@csrf
    <div><label class="font-semibold">Nama pena / nama publik</label><input name="pen_name" value="{{ old('pen_name') }}" required class="mt-1 w-full rounded-xl dark:bg-[#0f1117] dark:border-gray-700">@error('pen_name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror</div>
    <div><label class="font-semibold">Biografi singkat</label><textarea name="bio" rows="4" class="mt-1 w-full rounded-xl dark:bg-[#0f1117] dark:border-gray-700">{{ old('bio') }}</textarea></div>
    @if(config('features.author_kyc'))
        <div class="border-t pt-5"><p class="font-bold mb-4">Verifikasi finansial (fitur legacy aktif)</p><div class="space-y-4"><input name="id_card_number" required placeholder="16 digit NIK" class="w-full rounded-xl"><input type="file" name="id_card_file" required class="w-full"><input name="bank_name" required placeholder="Nama bank" class="w-full rounded-xl"><input name="bank_account" required placeholder="Nomor rekening" class="w-full rounded-xl"><input name="bank_holder_name" required placeholder="Nama pemilik rekening" class="w-full rounded-xl"></div></div>
    @else
        <div class="rounded-xl bg-violet-50 dark:bg-violet-900/20 p-4 text-sm text-violet-800 dark:text-violet-200">KTP, rekening bank, dan verifikasi KYC finansial tidak diperlukan untuk mengajukan naskah.</div>
    @endif
    <button class="w-full bg-violet-700 text-white rounded-xl py-3 font-bold">Buat Profil Penulis</button>
</form></div></div></x-public-layout>
