<x-public-layout>
    <div class="max-w-3xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Hubungi Kami</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Punya pertanyaan atau butuh bantuan teknis? Tim kami siap membantu.</p>
        </div>

        <div class="bg-white dark:bg-[#161615] rounded-3xl p-8 lg:p-12 border border-gray-100 dark:border-[#2d3147] shadow-sm">
            <form action="#" method="POST" class="space-y-6">
                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap</label>
                    <input type="text" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Masukkan nama Anda">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                    <input type="email" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="nama@email.com">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Pesan</label>
                    <textarea rows="5" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Tuliskan pertanyaan atau masalah yang Anda hadapi..."></textarea>
                </div>

                <button type="button" onclick="alert('Fitur pengiriman pesan akan segera hadir.')" class="w-full py-4 bg-black dark:bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-colors">
                    Kirim Pesan
                </button>
            </form>
        </div>
    </div>
</x-public-layout>
