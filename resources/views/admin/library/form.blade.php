<x-admin-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.library.index') }}" class="text-text-muted hover:text-text-primary transition-colors">
                <svg class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/></svg>
            </a>
            <h2 class="font-bold text-2xl text-text-primary">{{ $item->exists ? 'Edit Koleksi' : 'Tambah Koleksi' }}</h2>
        </div>
    </x-slot>

    <div class="max-w-5xl mx-auto space-y-6">
        @if($errors->any())
            <div class="bg-red-50 text-red-700 p-4 rounded-xl border border-red-200">
                <ul class="list-disc list-inside">
                    @foreach($errors->all() as $error)
                        <li>{{ $error }}</li>
                    @endforeach
                </ul>
            </div>
        @endif

        <form method="POST" enctype="multipart/form-data" action="{{ $item->exists ? route('admin.library.update', $item) : route('admin.library.store') }}" class="space-y-6">
            @csrf
            @if($item->exists) @method('PUT') @endif

            <!-- Section 1: Informasi Dasar -->
            <div class="bg-surface rounded-xl p-6 border border-border space-y-6">
                <h3 class="text-lg font-bold text-text-primary border-b border-border pb-3">Informasi Dasar</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Jenis Koleksi *</label>
                        <select name="type" required class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                            @foreach(config('library.types', ['book','journal','journal_issue','journal_article','article','proceeding','report','module','monograph','other']) as $type)
                                <option value="{{ $type }}" @selected(old('type', $item->type) === $type)>{{ App\Models\LibraryItem::getLocalizedType($type) }}</option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Judul *</label>
                        <input name="title" value="{{ old('title', $item->title) }}" required class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Deskripsi Singkat</label>
                        <textarea name="description" rows="2" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">{{ old('description', $item->description) }}</textarea>
                    </div>

                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Kreator / Penulis <span class="text-text-muted text-xs font-normal">(Pisahkan dengan koma)</span></label>
                        <input name="creators" value="{{ old('creators', $item->creators->pluck('name')->join(', ')) }}" placeholder="Contoh: Budi Santoso, Siti Aminah" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Penerbit</label>
                        <input name="publisher" value="{{ old('publisher', $item->publisher) }}" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Tahun Terbit</label>
                        <input type="number" name="publication_year" value="{{ old('publication_year', $item->publication_year) }}" min="1000" max="2100" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                </div>
            </div>

            <!-- Section 2: Metadata Lanjutan -->
            <div class="bg-surface rounded-xl p-6 border border-border space-y-6">
                <h3 class="text-lg font-bold text-text-primary border-b border-border pb-3">Metadata Lanjutan</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">ISBN</label>
                        <input name="isbn" value="{{ old('isbn', $item->isbn) }}" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">ISSN</label>
                        <input name="issn" value="{{ old('issn', $item->issn) }}" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">DOI</label>
                        <input name="doi" value="{{ old('doi', $item->doi) }}" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Bahasa</label>
                        <input name="language" value="{{ old('language', $item->language) }}" placeholder="Contoh: Indonesia" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Kata Kunci <span class="text-text-muted text-xs font-normal">(Pisahkan dengan koma)</span></label>
                        <input name="keywords" value="{{ old('keywords', $item->keywords) }}" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>
                    <div class="md:col-span-2">
                        <label class="block text-sm font-medium text-text-secondary mb-1">Kategori</label>
                        <div class="flex flex-wrap gap-4 mt-2 p-4 bg-background border border-border rounded-lg">
                            @foreach($categories as $category)
                                <label class="inline-flex items-center gap-2 cursor-pointer">
                                    <input type="checkbox" name="category_ids[]" value="{{ $category->id }}" @checked(in_array($category->id, old('category_ids', $item->categories->pluck('id')->all()))) class="rounded border-border text-primary focus:ring-primary bg-background">
                                    <span class="text-sm text-text-primary">{{ $category->name }}</span>
                                </label>
                            @endforeach
                        </div>
                    </div>
                </div>
            </div>

            <!-- Section 3: File & Akses -->
            <div class="bg-surface rounded-xl p-6 border border-border space-y-6">
                <h3 class="text-lg font-bold text-text-primary border-b border-border pb-3">Berkas & Kebijakan Akses</h3>

                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Berkas Utama (PDF/EPUB)</label>
                        <input type="file" name="publication_file" accept=".pdf,.epub" class="w-full bg-background border border-border rounded-lg p-2 text-text-primary focus:ring-primary focus:border-primary">
                        @if($item->primaryFile)
                            <p class="text-xs text-green-600 mt-1">Berkas sudah ada (unggah ulang untuk mengganti).</p>
                        @endif
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Atau URL Eksternal</label>
                        <input type="url" name="external_file_url" value="{{ old('external_file_url', $item->primaryFile?->external_url) }}" placeholder="https://..." class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                    </div>

                    <div class="md:col-span-2 flex items-center gap-2 mb-2">
                        <input type="hidden" name="download_allowed" value="0">
                        <input type="checkbox" id="download_allowed" name="download_allowed" value="1" @checked(old('download_allowed', $item->primaryFile?->download_allowed)) class="rounded border-border text-primary focus:ring-primary bg-background w-5 h-5">
                        <label for="download_allowed" class="text-sm font-medium text-text-primary cursor-pointer">Izinkan pengguna mengunduh berkas ini</label>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Tipe Sumber</label>
                        <select name="source_type" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                            <option value="local">Lokal P4I</option>
                            <option value="ojs" @selected(old('source_type', $item->source_type) === 'ojs')>OJS</option>
                            <option value="external" @selected(old('source_type', $item->source_type) === 'external')>Eksternal</option>
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Kebijakan Akses *</label>
                        <select name="access_policy" required class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                            @foreach(config('library.access_policies', ['public_read_only', 'public_read_download', 'registered_read_only', 'registered_read_download', 'manual_purchase', 'external']) as $policy)
                                <option value="{{ $policy }}" @selected(old('access_policy', $item->access_policy) === $policy)>
                                    {{ str($policy)->replace('_', ' ')->title() }}
                                </option>
                            @endforeach
                        </select>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Harga (Rp)</label>
                        <input type="number" name="price" value="{{ old('price', $item->price ?? 0) }}" min="0" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                        <p class="text-xs text-text-muted mt-1">Biarkan 0 untuk akses gratis.</p>
                    </div>

                    <div>
                        <label class="block text-sm font-medium text-text-secondary mb-1">Status Visibilitas</label>
                        <select name="status" class="w-full bg-background border border-border rounded-lg p-2.5 text-text-primary focus:ring-primary focus:border-primary">
                            <option value="draft" @selected(old('status', $item->status) === 'draft')>Draft (Sembunyikan)</option>
                            <option value="published" @selected(old('status', $item->status) === 'published')>Terbit (Publik)</option>
                            <option value="archived" @selected(old('status', $item->status) === 'archived')>Arsip</option>
                        </select>
                    </div>
                </div>
            </div>

            <div class="flex justify-end gap-4">
                <x-button type="button" href="{{ route('admin.library.index') }}" variant="secondary">Batal</x-button>
                <x-button type="submit" variant="primary">Simpan Koleksi</x-button>
            </div>
        </form>
    </div>
</x-admin-layout>
