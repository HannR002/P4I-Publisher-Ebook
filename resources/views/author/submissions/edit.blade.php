<x-author-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('author.submissions.index') }}" class="text-text-muted hover:text-text-secondary">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-text-primary leading-tight">
                {{ __('Revisi Pengajuan Naskah') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-12">
        @if($submission->status === 'revision_requested')
        <div class="mb-6 bg-orange-500/10 border-l-4 border-orange-500 p-4 rounded-md">
            <div class="flex">
                <div class="flex-shrink-0">
                    <svg class="h-5 w-5 text-orange-500" viewBox="0 0 20 20" fill="currentColor">
                        <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd" />
                    </svg>
                </div>
                <div class="ml-3">
                    <h3 class="text-sm font-medium text-orange-700 dark:text-orange-300">Naskah ini membutuhkan revisi.</h3>
                    <div class="mt-2 text-sm text-orange-700 dark:text-orange-200">
                        <p>Silakan perbarui form di bawah ini atau unggah dokumen PDF baru sesuai instruksi kurator, lalu klik "Kirim Ulang ke Kurator".</p>
                    </div>
                </div>
            </div>
        </div>
        @endif

        <form action="{{ route('author.submissions.update', $submission->id) }}" method="POST" enctype="multipart/form-data"
              x-data="{
                  submitting: false,
                  synopsis: `{{ old('synopsis', $submission->synopsis) }}`,
                  actionVal: '{{ $submission->status }}',
                  coverPreview: '{{ $submission->cover_preview_path ? asset('storage/' . $submission->cover_preview_path) : '' }}',
                  fileName: '',
                  fileExt: '',

                  handleCoverUpload(event) {
                      const file = event.target.files[0];
                      if (!file) return;
                      const reader = new FileReader();
                      reader.onload = (e) => { this.coverPreview = e.target.result; };
                      reader.readAsDataURL(file);
                  },

                  handlePdfUpload(event) {
                      const file = event.target.files[0];
                      if (!file) {
                          this.fileName = '';
                          this.fileExt = '';
                          return;
                      }
                      this.fileName = file.name;
                      this.fileExt = file.name.split('.').pop().toLowerCase();
                  },

                  submitForm(actionType) {
                      if(this.synopsis.length < 100) {
                          alert('Sinopsis minimal harus 100 karakter.');
                          return;
                      }
                      if(document.getElementById('manuscript_file').files.length > 0 && this.fileExt !== 'pdf') {
                          alert('Berkas harus berupa PDF murni.');
                          return;
                      }
                      this.actionVal = actionType;
                      this.submitting = true;
                      $nextTick(() => { this.$refs.form.submit(); });
                  }
              }"
              x-ref="form"
              class="bg-surface shadow-sm rounded-xl border border-border overflow-hidden">
            @csrf
            @method('PUT')

            <div class="px-6 py-8 sm:p-10 space-y-8">
                <!-- Info Utama -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text-primary border-b border-border pb-2 mb-4">Informasi Utama Buku</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-text-secondary">Judul Naskah <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" required value="{{ old('title', $submission->title) }}"
                                class="mt-1 shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-border bg-background text-text-primary rounded-md">
                            @error('title') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-text-secondary">Kategori / Genre <span class="text-red-500">*</span></label>
                            <select id="category_id" name="category_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-border bg-background text-text-primary focus:outline-none focus:ring-primary focus:border-primary sm:text-sm rounded-md">
                                <option value="" disabled>Pilih Kategori</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}" {{ (old('category_id', $submission->category_id) == $cat->id) ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="proposed_price" class="block text-sm font-medium text-text-secondary">Usulan Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-text-muted sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="proposed_price" id="proposed_price" required value="{{ old('proposed_price', $submission->proposed_price) }}" min="0" max="10000000"
                                    class="focus:ring-primary focus:border-primary block w-full pl-10 sm:text-sm border-border bg-background text-text-primary rounded-md">
                            </div>
                            @error('proposed_price') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="synopsis" class="block text-sm font-medium text-text-secondary">
                                Sinopsis & Blurb <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <textarea id="synopsis" name="synopsis" rows="5" required x-model="synopsis"
                                    class="shadow-sm focus:ring-primary focus:border-primary block w-full sm:text-sm border-border bg-background text-text-primary rounded-md"></textarea>
                                <div class="absolute bottom-2 right-2 text-xs" :class="synopsis.length < 100 ? 'text-red-500' : 'text-green-500'">
                                    <span x-text="synopsis.length"></span>/100 karakter min.
                                </div>
                            </div>
                            @error('synopsis') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>
                    </div>
                </div>

                <!-- Berkas -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-text-primary border-b border-border pb-2 mb-4">Berkas Pendukung</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">

                        <!-- Upload PDF -->
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-text-secondary mb-2">Berkas Naskah (PDF Baru)</label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-border border-dashed rounded-md bg-background hover:bg-surface transition-colors relative group">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-text-muted group-hover:text-primary transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-text-secondary justify-center">
                                        <label for="manuscript_file" class="relative cursor-pointer rounded-md font-medium text-primary hover:text-primary-hover focus-within:outline-none">
                                            <span>Ganti File PDF</span>
                                            <input id="manuscript_file" name="manuscript_file" type="file" class="sr-only" @change="handlePdfUpload" accept=".pdf">
                                        </label>
                                    </div>
                                    <p class="text-xs text-text-muted">Kosongkan jika tidak ingin mengubah naskah</p>
                                </div>
                            </div>

                            <!-- File Indicator -->
                            <div class="mt-3 flex items-center gap-2 text-sm" x-show="fileName !== ''" x-cloak>
                                <svg x-show="fileExt === 'pdf'" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg x-show="fileExt !== 'pdf'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span :class="fileExt === 'pdf' ? 'text-green-600 dark:text-green-400' : 'text-red-600 dark:text-red-400'" x-text="fileName"></span>
                            </div>

                            <div class="mt-2 text-sm text-text-muted flex items-center gap-2" x-show="fileName === ''">
                                <svg class="w-4 h-4 text-text-muted" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path></svg>
                                Menggunakan berkas sebelumnya
                            </div>

                            @error('manuscript_file') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <!-- Upload Cover Preview -->
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-text-secondary mb-2">Usulan Gambar Sampul (Opsional)</label>
                            <div class="flex items-center gap-6">
                                <div class="w-32 h-44 rounded-md border-2 border-border border-dashed bg-background flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <template x-if="coverPreview">
                                        <img :src="coverPreview" class="w-full h-full object-cover" alt="Preview Sampul">
                                    </template>
                                    <template x-if="!coverPreview">
                                        <span class="text-xs text-text-muted text-center px-2">Rasio 1:1.4 (Buku)</span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="cover_preview" @change="handleCoverUpload" accept=".jpg,.jpeg,.png"
                                        class="block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-primary/10 file:text-primary hover:file:bg-primary/20 transition-colors">
                                    <p class="mt-2 text-xs text-text-muted">Kosongkan jika tidak ingin mengubah sampul.</p>
                                    @error('cover_preview') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <input type="hidden" name="action" x-model="actionVal">
            <div class="px-6 py-4 bg-surface border-t border-border flex items-center justify-end gap-3">
                @if($submission->status === 'draft')
                <button type="button" @click="submitForm('draft')" x-bind:disabled="submitting"
                    class="inline-flex justify-center py-2.5 px-4 border border-border shadow-sm text-sm font-medium rounded-md text-text-primary bg-background hover:bg-surface-hover focus:outline-none disabled:opacity-50 transition-colors">
                    <span x-show="!submitting || actionVal !== 'draft'">Simpan Pembaruan Draf</span>
                    <span x-show="submitting && actionVal === 'draft'" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-text-muted" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
                @endif

                <button type="button" @click="submitForm('submit')" x-bind:disabled="submitting"
                    class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-primary hover:bg-primary-hover focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-primary disabled:opacity-50 transition-all">
                    <span x-show="!submitting || actionVal !== 'submit'">{{ $submission->status === 'draft' ? 'Kirim Naskah ke Kurator' : 'Kirim Ulang ke Kurator' }}</span>
                    <span x-show="submitting && actionVal === 'submit'" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Mengirim...
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-author-layout>
