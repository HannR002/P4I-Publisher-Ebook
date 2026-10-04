<x-public-layout>
    <div class="max-w-2xl mx-auto px-6 py-12">
        <div class="bg-surface rounded-3xl border border-border p-8">
            <h1 class="text-3xl font-black text-text-primary">Pusat Penerbitan Buku</h1>
            <p class="mt-2 text-text-secondary">Buat profil penulis untuk mengajukan naskah buku digital atau cetak.</p>
            <form action="{{ url('/author/register') }}" method="POST" enctype="multipart/form-data" class="mt-8 space-y-5">
                @csrf
                <div>
                    <label class="font-semibold text-text-primary">Nama pena / nama publik</label>
                    <input name="pen_name" value="{{ old('pen_name') }}" required class="mt-1 w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">
                    @error('pen_name')<p class="text-red-600 text-sm">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="font-semibold text-text-primary">Biografi singkat</label>
                    <textarea name="bio" rows="4" class="mt-1 w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">{{ old('bio') }}</textarea>
                </div>
                @if(config('features.author_kyc'))
                    <div class="border-t border-border pt-5">
                        <p class="font-bold mb-4 text-text-primary">Verifikasi finansial (fitur legacy aktif)</p>
                        <div class="space-y-4">
                            <input name="id_card_number" required placeholder="16 digit NIK" class="w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">
                            <input type="file" name="id_card_file" required class="w-full text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-xl file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                            <input name="bank_name" required placeholder="Nama bank" class="w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">
                            <input name="bank_account" required placeholder="Nomor rekening" class="w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">
                            <input name="bank_holder_name" required placeholder="Nama pemilik rekening" class="w-full rounded-xl bg-background border-border text-text-primary focus:ring-primary focus:border-primary">
                        </div>
                    </div>
                @else
                    <div class="rounded-xl bg-primary/10 p-4 text-sm text-primary">KTP, rekening bank, dan verifikasi KYC finansial tidak diperlukan untuk mengajukan naskah.</div>
                @endif
                <button class="w-full bg-primary hover:bg-primary-hover text-white rounded-xl py-3 font-bold transition-colors">Buat Profil Penulis</button>
            </form>
        </div>
    </div>
</x-public-layout>
