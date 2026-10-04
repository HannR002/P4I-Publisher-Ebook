<x-author-layout>
    <div class="max-w-7xl mx-auto py-6 sm:px-6 lg:px-8">
        <!-- Header CTA -->
        <div class="flex justify-between items-center mb-8 px-4 sm:px-0">
            <div>
                <h1 class="text-2xl font-bold text-text-primary">Dasbor Utama Kreator</h1>
                <p class="text-sm text-text-secondary mt-1">Selamat datang kembali, {{ $author->pen_name }}!</p>
            </div>
            <a href="{{ route('author.submissions.create') }}" class="inline-flex items-center px-4 py-2 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary transition-all">
                <svg class="-ml-1 mr-2 h-5 w-5" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4" />
                </svg>
                + Ajukan Naskah Baru
            </a>
        </div>

        <!-- 3 Metric Cards Grid -->
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6 px-4 sm:px-0 mb-10">
            <!-- Saldo Royalti -->
            <div class="bg-surface overflow-hidden shadow-sm rounded-xl border border-border flex flex-col relative group">
                <div class="absolute inset-0 bg-primary/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-primary/10 rounded-md p-3">
                            <svg class="h-6 w-6 text-primary" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-text-secondary truncate">Saldo Royalti Tersedia</dt>
                            <dd class="text-2xl font-bold text-text-primary mt-1">Rp {{ number_format($availableBalance, 0, ',', '.') }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-hover px-6 py-3 border-t border-border mt-auto">
                    <div class="text-sm">
                        <button disabled class="font-medium text-primary/50 cursor-not-allowed">Tarik Dana (Coming Soon)</button>
                    </div>
                </div>
            </div>

            <!-- Buku Terbit -->
            <div class="bg-surface overflow-hidden shadow-sm rounded-xl border border-border flex flex-col relative group">
                <div class="absolute inset-0 bg-green-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-green-500/10 rounded-md p-3">
                            <svg class="h-6 w-6 text-green-600 dark:text-green-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-text-secondary truncate">Total Buku Terbit</dt>
                            <dd class="text-2xl font-bold text-text-primary mt-1">{{ $publishedBooksCount }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-hover px-6 py-3 border-t border-border mt-auto">
                    <div class="text-sm">
                        <a href="{{ route('author.submissions.index') }}" class="font-medium text-green-600 dark:text-green-400 hover:text-green-700 dark:hover:text-green-300">Lihat Katalog →</a>
                    </div>
                </div>
            </div>

            <!-- Naskah Dalam Kurasi -->
            <div class="bg-surface overflow-hidden shadow-sm rounded-xl border border-border flex flex-col relative group">
                <div class="absolute inset-0 bg-amber-500/5 opacity-0 group-hover:opacity-100 transition-opacity"></div>
                <div class="p-6 relative">
                    <div class="flex items-center">
                        <div class="flex-shrink-0 bg-amber-500/10 rounded-md p-3">
                            <svg class="h-6 w-6 text-amber-600 dark:text-amber-400" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15.232 5.232l3.536 3.536m-2.036-5.036a2.5 2.5 0 113.536 3.536L6.5 21.036H3v-3.572L16.732 3.732z" />
                            </svg>
                        </div>
                        <div class="ml-4 w-0 flex-1">
                            <dt class="text-sm font-medium text-text-secondary truncate">Naskah Dalam Kurasi</dt>
                            <dd class="text-2xl font-bold text-text-primary mt-1">{{ $inReviewCount }}</dd>
                        </div>
                    </div>
                </div>
                <div class="bg-surface-hover px-6 py-3 border-t border-border mt-auto">
                    <div class="text-sm">
                        <a href="{{ route('author.submissions.index') }}" class="font-medium text-amber-600 dark:text-amber-400 hover:text-amber-700 dark:hover:text-amber-300">Pantau Status →</a>
                    </div>
                </div>
            </div>
        </div>

        <!-- Recent Submissions Table -->
        <div class="px-4 sm:px-0">
            <div class="bg-surface shadow-sm rounded-xl border border-border overflow-hidden">
                <div class="px-6 py-5 border-b border-border flex justify-between items-center">
                    <h3 class="text-lg leading-6 font-medium text-text-primary">Aktivitas Naskah Terbaru</h3>
                    <a href="{{ route('author.submissions.index') }}" class="text-sm text-primary hover:underline">Lihat Semua</a>
                </div>
                <div class="overflow-x-auto">
                    <table class="min-w-full divide-y divide-border">
                        <thead class="bg-surface-hover">
                            <tr>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Judul Naskah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Tanggal Ubah</th>
                                <th scope="col" class="px-6 py-3 text-left text-xs font-medium text-text-secondary uppercase tracking-wider">Status</th>
                                <th scope="col" class="px-6 py-3 text-right text-xs font-medium text-text-secondary uppercase tracking-wider">Aksi</th>
                            </tr>
                        </thead>
                        <tbody class="bg-surface divide-y divide-border">
                            @forelse($author->submissions()->latest()->take(5)->get() as $submission)
                            <tr>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <div class="text-sm font-medium text-text-primary">{{ $submission->title }}</div>
                                    <div class="text-sm text-text-secondary">{{ Str::limit($submission->synopsis, 30) }}</div>
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                                    {{ $submission->updated_at->format('d M Y H:i') }}
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap">
                                    <x-submission-status-badge :status="$submission->status" />
                                </td>
                                <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                                    <a href="{{ route('author.submissions.show', $submission->id) }}" class="text-primary hover:text-primary-hover">Detail</a>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="px-6 py-8 text-center text-text-secondary text-sm">
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
