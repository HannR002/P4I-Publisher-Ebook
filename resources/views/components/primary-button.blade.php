<button {{ $attributes->merge(['type' => 'submit', 'class' => 'inline-flex items-center justify-center px-4 py-4 bg-gradient-to-r from-indigo-600 to-purple-600 border border-transparent rounded-xl shadow-lg font-extrabold text-sm text-white uppercase tracking-widest hover:from-indigo-500 hover:to-purple-500 focus:outline-none focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2 focus:ring-offset-[#1a1d2e] transform transition-all hover:-translate-y-1']) }}>
    {{ $slot }}
</button>
