<x-guest-layout>
    <!-- Header -->
    <div class="mb-10 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Selamat Datang Kembali 👋</h1>
        <p class="text-gray-500 text-sm">Silakan masuk ke akun P4I Publisher Anda untuk melanjutkan.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-6" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}" class="space-y-5">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" :value="__('Alamat Email')" class="text-gray-700 font-semibold" />
            <x-text-input id="email" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@email.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div>
            <div class="flex justify-between items-center mb-2">
                <x-input-label for="password" :value="__('Kata Sandi')" class="text-gray-700 font-semibold" />
                @if (Route::has('password.request'))
                    <a class="text-sm font-semibold text-blue-600 hover:text-blue-500 transition-colors" href="{{ route('password.request') }}">
                        {{ __('Lupa sandi?') }}
                    </a>
                @endif
            </div>

            <x-text-input id="password" class="block w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block pt-1">
            <label for="remember_me" class="inline-flex items-center cursor-pointer group">
                <input id="remember_me" type="checkbox" class="rounded bg-white border-gray-300 text-blue-600 focus:ring-blue-500 focus:ring-offset-white w-5 h-5 transition-colors group-hover:border-blue-500" name="remember">
                <span class="ms-3 text-sm font-medium text-gray-500 group-hover:text-gray-700 transition-colors">{{ __('Ingat Saya') }}</span>
            </label>
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-extrabold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transform transition-all hover:-translate-y-1">
                {{ __('MASUK SEKARANG') }}
            </button>
        </div>

        <div class="mt-8 text-center text-sm text-gray-500">
            Belum punya akun?
            <a href="{{ route('register') }}" class="font-extrabold text-blue-600 hover:text-blue-500 transition-colors">
                Daftar Gratis
            </a>
        </div>
    </form>
</x-guest-layout>
