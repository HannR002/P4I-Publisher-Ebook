<x-author-layout>
    <div class="max-w-4xl mx-auto py-12 px-4 sm:px-6 lg:px-8">

        @if($author->kyc_status === 'pending')
        <!-- Status Pending -->
        <div class="bg-amber-50 dark:bg-amber-900/20 border border-amber-200 dark:border-amber-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-8 sm:p-10 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-amber-100 dark:bg-amber-900/50 mb-6">
                    <svg class="h-10 w-10 text-amber-600 dark:text-amber-400 animate-pulse" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-amber-900 dark:text-amber-100 mb-2">Peninjauan Identitas Sedang Berlangsung</h3>
                <p class="text-amber-700 dark:text-amber-300 max-w-xl mx-auto">
                    Terima kasih telah bergabung, <strong>{{ $author->pen_name }}</strong>. Tim kurator kami sedang memverifikasi dokumen KTP dan informasi rekening Anda. Proses ini biasanya memakan waktu 1x24 jam kerja.
                </p>
                <div class="mt-8">
                    <a href="{{ route('books.index') }}" class="inline-flex items-center px-4 py-2 border border-transparent text-sm font-medium rounded-md text-amber-700 bg-amber-100 hover:bg-amber-200 dark:text-amber-200 dark:bg-amber-900/50 dark:hover:bg-amber-900 focus:outline-none transition-colors">
                        Kembali ke Mode Pembaca
                    </a>
                </div>
            </div>
        </div>
        @elseif($author->kyc_status === 'rejected')
        <!-- Status Rejected -->
        <div class="bg-red-50 dark:bg-red-900/20 border border-red-200 dark:border-red-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-8 sm:p-10">
                <div class="flex items-center gap-4 mb-6">
                    <div class="flex items-center justify-center h-12 w-12 rounded-full bg-red-100 dark:bg-red-900/50">
                        <svg class="h-6 w-6 text-red-600 dark:text-red-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z" />
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-xl font-bold text-red-900 dark:text-red-100">Verifikasi Ditolak</h3>
                        <p class="text-red-700 dark:text-red-300 text-sm">Ada kendala dengan data yang Anda ajukan.</p>
                    </div>
                </div>

                <div class="bg-white dark:bg-[#1a1d24] rounded-xl p-6 border border-red-100 dark:border-red-900/50 mb-8">
                    <h4 class="text-sm font-semibold text-gray-900 dark:text-gray-100 mb-2 uppercase tracking-wide">Catatan dari Kurator:</h4>
                    <p class="text-gray-700 dark:text-gray-300 italic">"{{ $author->rejection_reason ?? 'Dokumen KTP buram atau nama di rekening tidak sesuai dengan identitas KTP.' }}"</p>
                </div>

                <!-- Form Perbaikan KYC -->
                <div x-data="{ submitting: false }">
                    <h4 class="text-lg font-bold text-gray-900 dark:text-white mb-4">Unggah Ulang Dokumen (Revisi)</h4>
                    <form action="#" method="POST" enctype="multipart/form-data" @submit="submitting = true" class="space-y-6">
                        @csrf
                        <div class="grid grid-cols-1 gap-6 sm:grid-cols-2">
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Pindaian KTP Baru</label>
                                <input type="file" name="id_card_file" required class="mt-1 block w-full text-sm text-gray-500 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300">
                            </div>
                            <div>
                                <label class="block text-sm font-medium text-gray-700 dark:text-gray-300">Nomor Rekening Benar</label>
                                <input type="text" name="bank_account" value="{{ $author->bank_account }}" class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-[#0f1117] rounded-md">
                            </div>
                        </div>
                        <div class="flex justify-end">
                            <button type="button" x-bind:disabled="submitting" class="inline-flex justify-center py-2 px-4 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-indigo-600 hover:bg-indigo-700 focus:outline-none disabled:opacity-50">
                                <span x-show="!submitting">Ajukan Ulang Verifikasi</span>
                                <span x-show="submitting">Mengirim...</span>
                            </button>
                        </div>
                    </form>
                </div>
            </div>
        </div>
        @elseif($author->kyc_status === 'verified')
        <!-- Status Verified -->
        <div class="bg-green-50 dark:bg-green-900/20 border border-green-200 dark:border-green-800 rounded-2xl shadow-sm overflow-hidden">
            <div class="px-6 py-8 sm:p-10 text-center">
                <div class="mx-auto flex items-center justify-center h-20 w-20 rounded-full bg-green-100 dark:bg-green-900/50 mb-6">
                    <svg class="h-10 w-10 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z" />
                    </svg>
                </div>
                <h3 class="text-2xl font-bold text-green-900 dark:text-green-100 mb-2">Selamat, Akun Anda Terverifikasi!</h3>
                <p class="text-green-700 dark:text-green-300 max-w-xl mx-auto mb-8">
                    Kemitraan Anda sebagai penulis di P4I Publisher telah disetujui. Anda sekarang dapat mulai mengunggah naskah dan memantau royalti Anda.
                </p>
                <div class="flex justify-center gap-4">
                    <a href="{{ route('author.dashboard') }}" class="inline-flex items-center px-6 py-3 border border-transparent text-base font-medium rounded-md text-white bg-green-600 hover:bg-green-700 transition-colors shadow-sm">
                        Masuk ke Dasbor Penulis
                    </a>
                    <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-6 py-3 border border-gray-300 dark:border-gray-600 shadow-sm text-base font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-[#1a1d24] hover:bg-gray-50 dark:hover:bg-gray-800 transition-colors">
                        Mulai Buat Pengajuan Naskah
                    </a>
                </div>
            </div>
        </div>
        @endif

    </div>
</x-author-layout>
