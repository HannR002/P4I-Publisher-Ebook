<x-guest-layout>
    <!-- Header -->
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Lupa Kata Sandi? 🔒</h1>
        <p class="text-gray-500 text-sm leading-relaxed">
            Tidak masalah. Cukup beri tahu kami alamat email Anda dan kami akan mengirimkan tautan untuk menyetel ulang kata sandi.
        </p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('password.email') }}" class="space-y-6">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors" type="email" name="email" :value="old('email')" required autofocus placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-extrabold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transform transition-all hover:-translate-y-1">
                {{ __('KIRIM TAUTAN RESET') }}
            </button>
        </div>

        <div class="mt-8 text-center text-sm">
            <a href="{{ route('login') }}" class="font-extrabold text-blue-600 hover:text-blue-500 transition-colors flex items-center justify-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" xmlns="http://www.w3.org/2000/svg"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
                Kembali ke Login
            </a>
        </div>
    </form>
</x-guest-layout>
