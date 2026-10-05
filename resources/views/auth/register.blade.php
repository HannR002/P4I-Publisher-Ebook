<x-guest-layout>
    <!-- Header -->
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Buat Akun Baru ✨</h1>
        <p class="text-gray-500 text-sm">Bergabunglah dengan ribuan pembaca setia P4I Digital Library hari ini.</p>
    </div>

    <form method="POST" action="{{ route('register') }}" class="space-y-5">
        @csrf

        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Nama Lengkap')" class="text-gray-700 font-semibold" />
            <x-text-input id="name" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors" type="text" name="name" :value="old('name')" required autofocus autocomplete="name" placeholder="John Doe" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors" type="email" name="email" :value="old('email')" required autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" class="text-gray-700 font-semibold" />
            <x-text-input id="password" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors"
                            type="password"
                            name="password"
                            required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div>
            <x-input-label for="password_confirmation" :value="__('Konfirmasi Kata Sandi')" class="text-gray-700 font-semibold" />
            <x-text-input id="password_confirmation" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-extrabold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transform transition-all hover:-translate-y-1">
                {{ __('DAFTAR SEKARANG') }}
            </button>
        </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            Sudah punya akun?
            <a href="{{ route('login') }}" class="font-extrabold text-blue-600 hover:text-blue-500 transition-colors">
                Masuk di sini
            </a>
        </div>
    </form>
</x-guest-layout>
