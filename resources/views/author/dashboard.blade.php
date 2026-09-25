<x-author-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header CTA -->
        <div class="flex justify-between items-center mb-8 px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-gray-900 dark:text-white">Dasbor Utama Kreator</h1>
                <p class="text-sm text-gray-500 dark:text-gray-400 mt-1">Selamat datang kembali, {{ $author->pen_name }}!</p>
            </div>
            <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 transition-all">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Ajukan Naskah Baru
            </a>
        </div>

        <!-- 3 Metric Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-4 sm:px-0 mb-10">
            <!-- Saldo Royalti -->
            <div class="bg-white dark:bg-[#1a1d24] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 flex flex-col relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-indigo-50 to-transparent dark:from-indigo-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-indigo-100 dark:bg-indigo-900/50 rounded-md p-3">
                            <svg class="h-6 w-6 text-indigo-600 dark:text-indigo-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Saldo Royalti Tersedia</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white mt-1">Rp {{ number_format($availableBalance, 0, ',', '.') }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-3 border-t border-gray-100 dark:border-gray-800 mt-auto">
                    <div class="text-sm">
                        <button disabled class="font-medium text-indigo-400 dark:text-indigo-500 cursor-not-allowed">Tarik Dana (Coming Soon)</button>
                    </div>
                </div>
            </div>

            <!-- Buku Terbit -->
            <div class="bg-white dark:bg-[#1a1d24] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 flex flex-col relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-green-50 to-transparent dark:from-green-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-100 dark:bg-green-900/50 rounded-md p-3">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Total Buku Terbit</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $publishedBooksCount }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-3 border-t border-gray-100 dark:border-gray-800 mt-auto">
                    <div class="text-sm">
                        <a href="{{ route('author.submissions.index') }}" class="font-medium text-green-600 hover:text-green-500 dark:text-green-400 dark:hover:text-green-300">Lihat Katalog →</a>
                    </div>
                </div>
            </div>

            <!-- Naskah Dalam Kurasi -->
            <div class="bg-white dark:bg-[#1a1d24] overflow-hidden shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 flex flex-col relative group">
                <div class="absolute inset-0 bg-gradient-to-br from-amber-50 to-transparent dark:from-amber-900/10 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-100 dark:bg-amber-900/50 rounded-md p-3">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-gray-500 dark:text-gray-400 truncate">Naskah Dalam Kurasi</dt>
                            <dd class="text-2xl font-bold text-gray-900 dark:text-white mt-1">{{ $inReviewCount }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-gray-50 dark:bg-gray-800/50 px-6 py-3 border-t border-gray-100 dark:border-gray-800 mt-auto">
                    <div class="text-sm">
                        <a href="{{ route('author.submissions.index') }}" class="font-medium text-amber-600 hover:text-amber-500 dark:text-amber-400 dark:hover:text-amber-300">Pantau Status →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions Table -->
        <div class="px-4 sm:px-0">
            <div class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
                <div class="px-6 py-5 border-b border-gray-200 dark:border-gray-800 flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-gray-900 dark:text-white">Aktivitas Naskah Terbaru</h3>
                    <a href="{{ route('author.submissions.index') }}" class="text-sm text-indigo-600 dark:text-indigo-400 hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-gray-200 dark:divide-gray-800">
                        <thead class="bg-gray-50 dark:bg-gray-900/50">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Judul Naskah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Tanggal Ubah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-gray-500 dark:text-gray-400 uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-white dark:bg-[#1a1d24] divide-y divide-gray-200 dark:divide-gray-800">
                            @forelse($author->submissions()->latest()->take(5)->get() as $submission)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-gray-900 dark:text-white">{{ $submission->title }}</div>
                                    <div class="text-sm text-gray-500 dark:text-gray-400">{{ Str::limit($submission->synopsis, 30) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-gray-500 dark:text-gray-400">
                                    {{ $submission->updated_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    @php
                                        $colors = [
                                            'draft' => 'bg-gray-100 text-gray-800 dark:bg-gray-900 dark:text-gray-300',
                                            'submitted' => 'bg-blue-100 text-blue-800 dark:bg-blue-900/50 dark:text-blue-300',
                                            'in_review' => 'bg-yellow-100 text-yellow-800 dark:bg-yellow-900/50 dark:text-yellow-300',
                                            'revision_requested' => 'bg-orange-100 text-orange-800 dark:bg-orange-900/50 dark:text-orange-300',
                                            'approved' => 'bg-green-100 text-green-800 dark:bg-green-900/50 dark:text-green-300',
                                            'published' => 'bg-emerald-100 text-emerald-800 dark:bg-emerald-900/50 dark:text-emerald-300',
                                            'rejected' => 'bg-red-100 text-red-800 dark:bg-red-900/50 dark:text-red-300',
                                        ];
                                        $labels = [
                                            'draft' => 'Draf',
                                            'submitted' => 'Terkirim',
                                            'in_review' => 'Dalam Peninjauan',
                                            'revision_requested' => 'Revisi Diminta',
                                            'approved' => 'Disetujui',
                                            'published' => 'Terbit',
                                            'rejected' => 'Ditolak',
                                        ];
                                    @endphp
                                    <span class="px-2 inline-flex text-xs leading-5 font-semibold rounded-full {{ $colors[$submission->status] ?? 'bg-gray-100 text-gray-800' }}">
                                        {{ $labels[$submission->status] ?? $submission->status }}
                                    </span>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('author.submissions.show', $submission->id) }}" class="text-indigo-600 dark:text-indigo-400 hover:text-indigo-900 dark:hover:text-indigo-300">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-gray-500 dark:text-gray-400 text-sm">
                                    Belum ada naskah yang diajukan. Mulai bagikan karya Anda!
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-author-layout>
