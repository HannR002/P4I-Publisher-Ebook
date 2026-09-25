<x-guest-layout>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Verifikasi Email ✉️</h1>
        <p class="text-gray-500 text-sm leading-relaxed">
            Terima kasih telah mendaftar! Sebelum memulai, dapatkah Anda memverifikasi alamat email Anda dengan mengeklik tautan yang baru saja kami kirimkan melalui email kepada Anda? Jika Anda tidak menerima email tersebut, kami dengan senang hati akan mengirimkan email lain.
        </p>
    </div>

    @if (session('status') == 'verification-link-sent')
        <div class="mb-6 p-4 rounded-xl bg-emerald-50 border border-emerald-100 text-emerald-700 font-medium text-sm">
            {{ __('Tautan verifikasi baru telah dikirimkan ke alamat email yang Anda berikan saat pendaftaran.') }}
        </div>
    @endif

    <div class="mt-8 flex flex-col sm:flex-row items-center justify-between gap-4">
        <form method="POST" action="{{ route('verification.send') }}" class="w-full sm:w-auto">
            @csrf
            <button type="submit" class="w-full sm:w-auto flex justify-center py-3.5 px-6 border border-transparent rounded-xl shadow-sm text-sm font-extrabold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transform transition-all hover:-translate-y-1">
                {{ __('Kirim Ulang Email Verifikasi') }}
            </button>
        </form>

        <form method="POST" action="{{ route('logout') }}" class="w-full sm:w-auto text-center">
            @csrf
            <button type="submit" class="text-sm font-extrabold text-gray-500 hover:text-gray-900 transition-colors focus:outline-none">
                {{ __('Keluar Akun') }}
            </button>
        </form>
    </div>
</x-guest-layout>
