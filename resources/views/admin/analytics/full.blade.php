<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center justify-between">
            <h2 class="font-bold text-2xl text-text-primary">Eksplorasi Analitik & Wawasan</h2>
            
            <form method="GET" action="{{ route('admin.analytics.full') }}" class="flex items-center gap-2">
                <label for="period" class="text-sm font-bold text-text-primary">Periode:</label>
                <select name="period" id="period" onchange="this.form.submit()" class="rounded-lg border-border bg-background px-3 py-1.5 text-sm focus:ring-primary focus:border-primary transition-colors font-medium">
                    <option value="7" {{ $period == 7 ? 'selected' : '' }}>7 Hari Terakhir</option>
                    <option value="30" {{ $period == 30 ? 'selected' : '' }}>30 Hari Terakhir</option>
                    <option value="90" {{ $period == 90 ? 'selected' : '' }}>90 Hari Terakhir</option>
                </select>
            </form>
        </div>
    </x-slot>

    <div class="space-y-8">
        
        <!-- Engagement Over Time (Placeholder for Chart) -->
        <section class="bg-surface rounded-xl border border-border p-6 md:p-8">
            <h3 class="text-lg font-bold text-text-primary mb-6">Tren Interaksi ({{ $period }} Hari Terakhir)</h3>
            <div class="h-64 flex items-end gap-2 overflow-x-auto pb-2 relative min-w-full">
                @if($engagementOverTime->isEmpty())
                    <div class="absolute inset-0 flex items-center justify-center text-text-muted italic">
                        Belum ada data interaksi (baca/unduh).
                    </div>
                @else
                    @php
                        $maxTotal = $engagementOverTime->max('total') ?: 1;
                        $groupedByDate = $engagementOverTime->groupBy('date');
                    @endphp
                    @foreach($groupedByDate as $date => $events)
                        @php
                            $reads = $events->where('event_type', 'read_start')->sum('total');
                            $downloads = $events->where('event_type', 'download')->sum('total');
                            $total = $reads + $downloads;
                            $heightPercent = min(100, ($total / $maxTotal) * 100);
                        @endphp
                        <div class="flex-1 min-w-[12px] md:min-w-[24px] group flex flex-col justify-end relative h-full">
                            <div class="w-full bg-primary/20 hover:bg-primary/40 rounded-t-sm transition-colors relative flex flex-col justify-end" style="height: {{ $heightPercent }}%;">
                                @if($downloads > 0)
                                    <div class="w-full bg-primary/60" style="height: {{ ($downloads/$total)*100 }}%;"></div>
                                @endif
                                <div class="opacity-0 group-hover:opacity-100 absolute bottom-full left-1/2 -translate-x-1/2 mb-2 bg-text-primary text-surface text-xs font-medium py-1 px-2 rounded whitespace-nowrap z-10 transition-opacity">
                                    {{ \Carbon\Carbon::parse($date)->format('d M') }}: {{ $reads }} Baca, {{ $downloads }} Unduh
                                </div>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
            <div class="mt-4 flex gap-4 text-sm text-text-muted justify-center">
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-primary/20"></span> Baca E-Book/Jurnal
                </div>
                <div class="flex items-center gap-1">
                    <span class="w-3 h-3 rounded-full bg-primary/60"></span> Unduh PDF
                </div>
            </div>
        </section>

        <!-- Kinerja Konten -->
        <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[500px]">
                <div class="p-4 border-b border-border bg-background">
                    <h3 class="font-bold text-text-primary">Paling Banyak Dibaca</h3>
                </div>
                <div class="overflow-y-auto flex-1 p-0">
                    <ul class="divide-y divide-border">
                        @forelse($mostRead as $item)
                            <li class="p-4 hover:bg-surface-hover/30 transition-colors flex justify-between items-center">
                                <div class="pr-4">
                                    <a href="{{ route('admin.library.edit', $item) }}" class="font-medium text-primary hover:underline line-clamp-1" title="{{ $item->title }}">{{ $item->title }}</a>
                                    <span class="text-xs text-text-secondary">{{ App\Models\LibraryItem::getLocalizedType($item->type) }}</span>
                                </div>
                                <span class="font-bold text-lg text-text-primary shrink-0">{{ $item->reads_count }}x</span>
                            </li>
                        @empty
                            <li class="p-8 text-center text-text-muted">Belum ada data interaksi.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[500px]">
                <div class="p-4 border-b border-border bg-background">
                    <h3 class="font-bold text-text-primary">Paling Banyak Diunduh</h3>
                </div>
                <div class="overflow-y-auto flex-1 p-0">
                    <ul class="divide-y divide-border">
                        @forelse($mostDownloaded as $item)
                            <li class="p-4 hover:bg-surface-hover/30 transition-colors flex justify-between items-center">
                                <div class="pr-4">
                                    <a href="{{ route('admin.library.edit', $item) }}" class="font-medium text-primary hover:underline line-clamp-1" title="{{ $item->title }}">{{ $item->title }}</a>
                                    <span class="text-xs text-text-secondary">{{ App\Models\LibraryItem::getLocalizedType($item->type) }}</span>
                                </div>
                                <span class="font-bold text-lg text-text-primary shrink-0">{{ $item->downloads_count }}x</span>
                            </li>
                        @empty
                            <li class="p-8 text-center text-text-muted">Belum ada data unduhan.</li>
                        @endforelse
                    </ul>
                </div>
            </section>

            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[500px]">
                <div class="p-4 border-b border-border bg-background">
                    <h3 class="font-bold text-text-primary flex items-center gap-2">
                        <svg class="w-4 h-4 text-orange-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M12.395 2.553a1 1 0 00-1.45-.385c-.345.23-.614.558-.822.88-.214.33-.403.713-.57 1.116-.334.804-.614 1.768-.84 2.734a31.365 31.365 0 00-.613 3.58 2.64 2.64 0 01-.945-1.067c-.328-.68-.398-1.534-.398-2.654A1 1 0 005.05 6.05 6.981 6.981 0 003 11a7 7 0 1011.95-4.95c-.592-.591-.98-.985-1.348-1.467-.363-.476-.724-1.063-1.207-2.03zM12.12 15.12A3 3 0 017 13s.879.5 2.5.5c0-1 .5-4 1.25-4.5.5 1 .786 1.293 1.371 1.879A2.99 2.99 0 0113 13a2.99 2.99 0 01-.879 2.121z" clip-rule="evenodd"/></svg>
                        Trending
                    </h3>
                </div>
                <div class="overflow-y-auto flex-1 p-0">
                    <ul class="divide-y divide-border">
                        @forelse($trending as $item)
                            <li class="p-4 hover:bg-surface-hover/30 transition-colors flex justify-between items-center">
                                <div class="pr-4">
                                    <a href="{{ route('admin.library.edit', $item) }}" class="font-medium text-primary hover:underline line-clamp-1" title="{{ $item->title }}">{{ $item->title }}</a>
                                    <span class="text-xs text-text-secondary">{{ App\Models\LibraryItem::getLocalizedType($item->type) }}</span>
                                </div>
                                <div class="shrink-0 text-right">
                                    <span class="block font-bold text-lg text-text-primary">{{ $item->recent_count }}</span>
                                    @php $diff = $item->recent_count - $item->previous_count; @endphp
                                    <span class="block text-xs font-semibold {{ $diff > 0 ? 'text-green-600' : 'text-text-muted' }}">
                                        {{ $diff > 0 ? '+' : '' }}{{ $diff }}
                                    </span>
                                </div>
                            </li>
                        @empty
                            <li class="p-8 text-center text-text-muted">Belum ada sinyal trending.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>

        <!-- Analitik Keuangan & Publikasi -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[400px]">
                <div class="p-5 border-b border-border bg-background">
                    <h3 class="text-lg font-bold text-text-primary">Analitik Transaksi Order Manual</h3>
                    <p class="text-xs text-text-muted mt-1">Berdasarkan data {{ $period }} hari terakhir.</p>
                </div>
                <div class="p-5 grid grid-cols-2 gap-4">
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <div class="text-text-muted text-xs font-bold uppercase">Realized Revenue</div>
                        <div class="text-2xl font-black text-primary mt-1">Rp{{ number_format((float)$payments['realized_revenue'], 0, ',', '.') }}</div>
                    </div>
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <div class="text-text-muted text-xs font-bold uppercase">Terverifikasi (Sukses)</div>
                        <div class="text-2xl font-black text-text-primary mt-1">{{ $payments['verified'] }} <span class="text-sm font-normal text-text-muted">order</span></div>
                    </div>
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <div class="text-text-muted text-xs font-bold uppercase">Menunggu Pembayaran</div>
                        <div class="text-2xl font-black text-text-primary mt-1">{{ $payments['awaiting_payment'] }}</div>
                    </div>
                    <div class="bg-background rounded-lg p-4 border border-border">
                        <div class="text-text-muted text-xs font-bold uppercase">Bukti Menunggu Verifikasi</div>
                        <div class="text-2xl font-black text-orange-600 mt-1">{{ $payments['payment_submitted'] }}</div>
                    </div>
                </div>
                @if($paymentBreakdown->isNotEmpty())
                    <div class="px-5 pb-5">
                        <h4 class="text-xs font-bold text-text-muted uppercase mb-2">Breakdown Metode Pembayaran (Verified)</h4>
                        <ul class="space-y-2">
                            @foreach($paymentBreakdown as $b)
                                <li class="flex justify-between items-center text-sm">
                                    <span class="text-text-primary font-medium">{{ $b->name }}</span>
                                    <div class="text-right">
                                        <span class="font-bold text-text-primary">Rp{{ number_format((float)$b->total_revenue, 0, ',', '.') }}</span>
                                        <span class="text-text-muted text-xs ml-1">({{ $b->total_orders }}x)</span>
                                    </div>
                                </li>
                            @endforeach
                        </ul>
                    </div>
                @endif
            </section>

            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[400px]">
                <div class="p-5 border-b border-border bg-background">
                    <h3 class="text-lg font-bold text-text-primary">Analitik Pipeline Publikasi</h3>
                    <p class="text-xs text-text-muted mt-1">Status naskah (Book Submission) secara keseluruhan.</p>
                </div>
                <div class="p-5">
                    <div class="grid grid-cols-2 md:grid-cols-3 gap-4 mb-6">
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-text-primary">{{ $publishing['submitted'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Diajukan</div>
                        </div>
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-orange-500">{{ $publishing['in_review'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Sedang Review</div>
                        </div>
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-yellow-600">{{ $publishing['revision_requested'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Perlu Revisi</div>
                        </div>
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-blue-600">{{ $publishing['approved'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Disetujui</div>
                        </div>
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-green-600">{{ $publishing['published'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Terbit</div>
                        </div>
                        <div class="bg-background rounded-lg p-3 border border-border text-center">
                            <div class="text-2xl font-black text-red-600">{{ $publishing['rejected'] }}</div>
                            <div class="text-text-muted text-xs font-bold mt-1">Ditolak</div>
                        </div>
                    </div>
                    
                    @if($oldestPendingSubmission)
                        <div class="bg-surface-hover rounded-lg p-4 border border-border">
                            <div class="text-xs font-bold text-text-muted uppercase mb-1">Naskah Terlama Menunggu Review</div>
                            <div class="font-bold text-text-primary line-clamp-1">{{ $oldestPendingSubmission->title }}</div>
                            <div class="text-sm text-text-secondary mt-1">Diajukan: {{ $oldestPendingSubmission->created_at->format('d M Y') }} ({{ $oldestPendingSubmission->created_at->diffForHumans() }})</div>
                            <a href="{{ route('admin.submissions.show', $oldestPendingSubmission) }}" class="inline-block mt-2 text-primary hover:underline text-xs font-bold">Tinjau Naskah &rarr;</a>
                        </div>
                    @endif
                </div>
            </section>
        </div>

        <!-- Wawasan Pencarian & Content Health -->
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-8">
            <div class="space-y-8">
                <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[300px]">
                    <div class="p-4 border-b border-border bg-background">
                        <h3 class="font-bold text-text-primary">Top Pencarian ({{ $period }} Hari)</h3>
                    </div>
                    <div class="overflow-y-auto flex-1 p-0">
                        <ul class="divide-y divide-border">
                            @forelse($topSearches as $search)
                                <li class="p-3 hover:bg-surface-hover/30 transition-colors flex justify-between items-center px-4">
                                    <span class="font-medium text-text-primary pr-4">{{ $search->search_term }}</span>
                                    <span class="text-sm font-semibold text-text-secondary bg-surface-hover px-3 py-1 rounded-full">{{ $search->total }} kali</span>
                                </li>
                            @empty
                                <li class="p-8 text-center text-text-muted">Belum ada data pencarian.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>

                <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[300px]">
                    <div class="p-4 border-b border-border bg-background">
                        <h3 class="font-bold text-red-600">Pencarian Tanpa Hasil (Zero-result)</h3>
                    </div>
                    <div class="overflow-y-auto flex-1 p-0">
                        <ul class="divide-y divide-border">
                            @forelse($zeroSearches as $search)
                                <li class="p-3 hover:bg-surface-hover/30 transition-colors flex justify-between items-center px-4">
                                    <span class="font-medium text-text-primary pr-4">{{ $search->search_term }}</span>
                                    <span class="text-sm font-semibold text-red-600 bg-red-50 px-3 py-1 rounded-full">{{ $search->total }} kali gagal</span>
                                </li>
                            @empty
                                <li class="p-8 text-center text-text-muted">Semua pencarian membuahkan hasil.</li>
                            @endforelse
                        </ul>
                    </div>
                </section>
            </div>

            <section class="bg-surface rounded-xl border border-border overflow-hidden flex flex-col h-[632px]">
                <div class="p-5 border-b border-border bg-background">
                    <h3 class="text-lg font-bold text-text-primary">Content Health Dashboard</h3>
                    <p class="text-xs text-text-muted mt-1">Audit kelengkapan metadata dan performa publikasi.</p>
                </div>
                <div class="p-5 overflow-y-auto flex-1">
                    <div class="space-y-4">
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">Cover Kosong</span>
                            <span class="font-bold {{ $contentIssues['missing_cover'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $contentIssues['missing_cover'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">File Digital Kosong</span>
                            <span class="font-bold {{ $contentIssues['missing_file'] > 0 ? 'text-red-600' : 'text-green-600' }}">{{ $contentIssues['missing_file'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">Metadata Perlu Dilengkapi (Deskripsi/Abstrak)</span>
                            <span class="font-bold {{ $contentIssues['missing_description'] > 0 ? 'text-orange-500' : 'text-green-600' }}">{{ $contentIssues['missing_description'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">Buku Tanpa ISBN</span>
                            <span class="font-bold {{ $contentIssues['missing_isbn'] > 0 ? 'text-orange-500' : 'text-green-600' }}">{{ $contentIssues['missing_isbn'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">Jurnal Tanpa ISSN</span>
                            <span class="font-bold {{ $contentIssues['missing_issn'] > 0 ? 'text-orange-500' : 'text-green-600' }}">{{ $contentIssues['missing_issn'] }}</span>
                        </div>
                        <div class="flex justify-between items-center p-3 bg-background border border-border rounded-lg">
                            <span class="font-medium text-text-primary">Status Draft (Belum Terbit)</span>
                            <span class="font-bold text-text-secondary">{{ $contentIssues['drafts'] }}</span>
                        </div>
                    </div>
                    
                    <h4 class="mt-8 mb-4 font-bold text-text-primary border-b border-border pb-2">Konten Jarang Dibaca (> 30 Hari)</h4>
                    <ul class="divide-y divide-border">
                        @forelse($rarelyRead as $item)
                            <li class="py-3 flex justify-between items-center">
                                <div>
                                    <a href="{{ route('admin.library.edit', $item) }}" class="font-medium text-primary hover:underline line-clamp-1" title="{{ $item->title }}">{{ $item->title }}</a>
                                    <div class="text-xs text-text-secondary mt-1">Terbit: {{ \Carbon\Carbon::parse($item->published_at)->diffForHumans(null, true) }} lalu</div>
                                </div>
                                <span class="font-bold text-sm {{ $item->reads_count == 0 ? 'text-red-500' : 'text-orange-500' }} shrink-0 ml-4">{{ $item->reads_count }}x dibaca</span>
                            </li>
                        @empty
                            <li class="py-8 text-center text-text-muted">Semua publikasi Anda cukup diminati pembaca.</li>
                        @endforelse
                    </ul>
                </div>
            </section>
        </div>

    </div>
</x-admin-layout>
