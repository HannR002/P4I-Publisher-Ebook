<x-guest-layout>
    <div class="mb-8 text-center lg:text-left">
        <h1 class="text-3xl font-extrabold text-gray-900 mb-2">Keamanan Lanjutan 🔒</h1>
        <p class="text-gray-500 text-sm leading-relaxed">
            Area ini diamankan dengan ketat. Harap konfirmasikan kata sandi Anda sebelum melanjutkan.
        </p>
    </div>

    <form method="POST" action="{{ route('password.confirm') }}" class="space-y-6">
        @csrf

        <!-- Password -->
        <div>
            <x-input-label for="password" :value="__('Kata Sandi')" class="text-gray-700 font-semibold" />
            <x-text-input id="password" class="block mt-2 w-full bg-gray-50 border-gray-200 text-gray-900 focus:border-blue-500 focus:ring-blue-500 rounded-xl px-4 py-3 shadow-sm transition-colors"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="pt-4">
            <button type="submit" class="w-full flex justify-center py-4 px-4 border border-transparent rounded-xl shadow-sm text-sm font-extrabold text-white bg-black hover:bg-gray-800 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-blue-500 focus:ring-offset-white transform transition-all hover:-translate-y-1">
                {{ __('KONFIRMASI KATA SANDI') }}
            </button>
        </div>
    </form>
</x-guest-layout>
