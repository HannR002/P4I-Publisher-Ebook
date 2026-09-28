<x-public-layout>
    <div class="max-w-4xl mx-auto px-4 sm:px-6 lg:px-8 py-16">
        <div class="text-center mb-12">
            <h1 class="text-4xl lg:text-5xl font-black text-gray-900 dark:text-white tracking-tight mb-4">Kirim Naskah Anda</h1>
            <p class="text-lg text-gray-600 dark:text-gray-400">Bergabunglah dengan ratusan penulis lainnya dan publikasikan karya Anda melalui platform P4I.</p>
        </div>
        
        <div class="bg-white dark:bg-[#161615] rounded-3xl p-8 lg:p-12 border border-gray-100 dark:border-[#2d3147] shadow-sm">
            
            <div class="mb-10 p-6 bg-blue-50 dark:bg-indigo-900/30 rounded-2xl border border-blue-100 dark:border-indigo-800">
                <h3 class="text-xl font-bold text-blue-900 dark:text-indigo-300 mb-3">Keuntungan Menerbitkan di Sini:</h3>
                <ul class="list-disc pl-5 space-y-2 text-blue-800 dark:text-indigo-200">
                    <li>Proteksi penuh terhadap pembajakan (DRM Technology).</li>
                    <li>Transparansi penjualan dan royalti.</li>
                    <li>Akses ke ribuan pembaca terdaftar.</li>
                    <li>Sistem pembayaran langsung dan aman.</li>
                </ul>
            </div>

            <form action="#" method="POST" class="space-y-6">
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Nama Lengkap</label>
                        <input type="text" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Nama Anda">
                    </div>
                    <div>
                        <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Email</label>
                        <input type="email" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Email untuk komunikasi">
                    </div>
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Judul Naskah</label>
                    <input type="text" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Judul buku Anda">
                </div>

                <div>
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Sinopsis Singkat</label>
                    <textarea rows="4" class="w-full rounded-xl border-gray-300 dark:border-[#3E3E3A] bg-white dark:bg-[#1a1d2e] dark:text-white focus:border-blue-500 focus:ring-blue-500 shadow-sm" placeholder="Ceritakan secara singkat tentang isi buku ini..."></textarea>
                </div>

                <div class="mb-6">
                    <label class="block text-sm font-semibold text-gray-700 dark:text-gray-300 mb-2">Unggah Draft / Contoh Bab (PDF/DOCX)</label>
                    <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-[#3E3E3A] border-dashed rounded-xl hover:bg-gray-50 dark:hover:bg-[#1a1d2e] transition-colors cursor-pointer">
                        <div class="space-y-1 text-center">
                            <svg class="mx-auto h-12 w-12 text-gray-400" stroke="currentColor" fill="none" viewBox="0 0 48 48" aria-hidden="true">
                                <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                            </svg>
                            <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                <span class="relative cursor-pointer bg-transparent rounded-md font-medium text-blue-600 dark:text-indigo-400 hover:text-blue-500 focus-within:outline-none focus-within:ring-2 focus-within:ring-offset-2 focus-within:ring-blue-500">
                                    <span>Pilih file</span>
                                </span>
                                <p class="pl-1">atau tarik file ke sini</p>
                            </div>
                            <p class="text-xs text-gray-500">PDF atau DOCX maksimal 10MB</p>
                        </div>
                    </div>
                </div>
                
                <button type="button" onclick="alert('Fitur pengiriman naskah akan segera hadir.')" class="w-full py-4 bg-black dark:bg-indigo-600 text-white font-bold rounded-xl shadow-sm hover:bg-gray-800 dark:hover:bg-indigo-500 transition-colors">
                    Kirim Naskah Saya
                </button>
            </form>
        </div>
    </div>
</x-public-layout>
