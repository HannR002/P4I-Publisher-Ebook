<x-author-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('author.submissions.index') }}" class="text-gray-400 hover:text-gray-600 dark:hover:text-gray-300">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <h2 class="font-semibold text-xl text-gray-800 dark:text-gray-200 leading-tight">
                {{ __('Formulir Pengajuan Naskah') }}
            </h2>
        </div>
    </x-slot>

    <div class="max-w-4xl mx-auto pb-12">
        <form action="{{ route('author.submissions.store') }}" method="POST" enctype="multipart/form-data" 
              x-data="{ 
                  submitting: false, 
                  synopsis: '{{ old('synopsis') }}', 
                  actionVal: 'draft',
                  coverPreview: null,
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
              class="bg-white dark:bg-[#1a1d24] shadow-sm rounded-xl border border-gray-100 dark:border-gray-800 overflow-hidden">
            @csrf
            
            <div class="px-6 py-8 sm:p-10 space-y-8">
                <!-- Info Utama -->
                <div>
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-2 mb-4">Informasi Utama Buku</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-4 sm:grid-cols-2">
                        <div class="sm:col-span-2">
                            <label for="title" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Judul Naskah <span class="text-red-500">*</span></label>
                            <input type="text" name="title" id="title" required value="{{ old('title') }}"
                                class="mt-1 shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-[#0f1117] dark:text-white rounded-md">
                            @error('title') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="category_id" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Kategori / Genre <span class="text-red-500">*</span></label>
                            <select id="category_id" name="category_id" required
                                class="mt-1 block w-full pl-3 pr-10 py-2 text-base border-gray-300 dark:border-gray-700 dark:bg-[#0f1117] dark:text-white focus:outline-none focus:ring-indigo-500 focus:border-indigo-500 sm:text-sm rounded-md">
                                <option value="" disabled selected>Pilih Kategori</option>
                                @foreach(\App\Models\Category::all() as $cat)
                                    <option value="{{ $cat->id }}" {{ old('category_id') == $cat->id ? 'selected' : '' }}>{{ $cat->name }}</option>
                                @endforeach
                            </select>
                            @error('category_id') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div>
                            <label for="proposed_price" class="block text-sm font-medium text-gray-700 dark:text-gray-300">Usulan Harga Jual (Rp) <span class="text-red-500">*</span></label>
                            <div class="mt-1 relative rounded-md shadow-sm">
                                <div class="absolute inset-y-0 left-0 pl-3 flex items-center pointer-events-none">
                                    <span class="text-gray-500 sm:text-sm">Rp</span>
                                </div>
                                <input type="number" name="proposed_price" id="proposed_price" required value="{{ old('proposed_price') }}" min="0" max="10000000"
                                    class="focus:ring-indigo-500 focus:border-indigo-500 block w-full pl-10 sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-[#0f1117] dark:text-white rounded-md">
                            </div>
                            <p class="mt-1 text-xs text-gray-500">Harga final akan diputuskan bersama kurator.</p>
                            @error('proposed_price') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <div class="sm:col-span-2">
                            <label for="synopsis" class="block text-sm font-medium text-gray-700 dark:text-gray-300">
                                Sinopsis & Blurb <span class="text-red-500">*</span>
                            </label>
                            <div class="mt-1 relative">
                                <textarea id="synopsis" name="synopsis" rows="5" required x-model="synopsis"
                                    class="shadow-sm focus:ring-indigo-500 focus:border-indigo-500 block w-full sm:text-sm border-gray-300 dark:border-gray-700 dark:bg-[#0f1117] dark:text-white rounded-md"></textarea>
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
                    <h3 class="text-lg font-medium leading-6 text-gray-900 dark:text-white border-b border-gray-200 dark:border-gray-800 pb-2 mb-4">Berkas Pendukung</h3>
                    <div class="grid grid-cols-1 gap-y-6 gap-x-8 sm:grid-cols-2">
                        
                        <!-- Upload PDF -->
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Berkas Naskah (PDF) <span class="text-red-500">*</span></label>
                            <div class="mt-1 flex justify-center px-6 pt-5 pb-6 border-2 border-gray-300 dark:border-gray-700 border-dashed rounded-md bg-gray-50 dark:bg-[#0f1117] hover:bg-gray-100 dark:hover:bg-[#1a1d24] transition-colors relative group">
                                <div class="space-y-1 text-center">
                                    <svg class="mx-auto h-12 w-12 text-gray-400 group-hover:text-indigo-500 transition-colors" stroke="currentColor" fill="none" viewBox="0 0 48 48">
                                        <path d="M28 8H12a4 4 0 00-4 4v20m32-12v8m0 0v8a4 4 0 01-4 4H12a4 4 0 01-4-4v-4m32-4l-3.172-3.172a4 4 0 00-5.656 0L28 28M8 32l9.172-9.172a4 4 0 015.656 0L28 28m0 0l4 4m4-24h8m-4-4v8m-12 4h.02" stroke-width="2" stroke-linecap="round" stroke-linejoin="round" />
                                    </svg>
                                    <div class="flex text-sm text-gray-600 dark:text-gray-400 justify-center">
                                        <label for="manuscript_file" class="relative cursor-pointer rounded-md font-medium text-indigo-600 dark:text-indigo-400 hover:text-indigo-500 focus-within:outline-none">
                                            <span>Pilih File PDF</span>
                                            <input id="manuscript_file" name="manuscript_file" type="file" required class="sr-only" @change="handlePdfUpload" accept=".pdf">
                                        </label>
                                    </div>
                                    <p class="text-xs text-gray-500">Hanya PDF murni. Maks: 50MB</p>
                                </div>
                            </div>
                            
                            <!-- File Indicator -->
                            <div class="mt-3 flex items-center gap-2 text-sm" x-show="fileName !== ''" x-cloak>
                                <svg x-show="fileExt === 'pdf'" class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <svg x-show="fileExt !== 'pdf'" class="w-5 h-5 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                                <span :class="fileExt === 'pdf' ? 'text-green-700 dark:text-green-400' : 'text-red-700 dark:text-red-400'" x-text="fileName"></span>
                            </div>

                            @error('manuscript_file') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                        </div>

                        <!-- Upload Cover Preview -->
                        <div class="sm:col-span-1">
                            <label class="block text-sm font-medium text-gray-700 dark:text-gray-300 mb-2">Usulan Gambar Sampul (Opsional)</label>
                            <div class="flex items-center gap-6">
                                <div class="w-32 h-44 rounded-md border-2 border-gray-300 dark:border-gray-700 border-dashed bg-gray-50 dark:bg-[#0f1117] flex items-center justify-center overflow-hidden flex-shrink-0">
                                    <template x-if="coverPreview">
                                        <img :src="coverPreview" class="w-full h-full object-cover" alt="Preview Sampul">
                                    </template>
                                    <template x-if="!coverPreview">
                                        <span class="text-xs text-gray-400 text-center px-2">Rasio 1:1.4 (Buku)</span>
                                    </template>
                                </div>
                                <div class="flex-1">
                                    <input type="file" name="cover_preview" @change="handleCoverUpload" accept=".jpg,.jpeg,.png"
                                        class="block w-full text-sm text-gray-500 dark:text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-md file:border-0 file:text-sm file:font-semibold file:bg-indigo-50 file:text-indigo-700 hover:file:bg-indigo-100 dark:file:bg-indigo-900/30 dark:file:text-indigo-300 transition-colors">
                                    <p class="mt-2 text-xs text-gray-500">Format: JPG, PNG. Maks: 2MB.</p>
                                    @error('cover_preview') <p class="mt-2 text-sm text-red-600 dark:text-red-400">{{ $message }}</p> @enderror
                                </div>
                            </div>
                        </div>

                    </div>
                </div>
            </div>

            <!-- Action Buttons -->
            <input type="hidden" name="action" x-model="actionVal">
            <div class="px-6 py-4 bg-gray-50 dark:bg-[#1a1d24] border-t border-gray-200 dark:border-gray-800 flex items-center justify-end gap-3">
                <button type="button" @click="submitForm('draft')" x-bind:disabled="submitting" 
                    class="inline-flex justify-center py-2.5 px-4 border border-gray-300 dark:border-gray-600 shadow-sm text-sm font-medium rounded-md text-gray-700 dark:text-gray-200 bg-white dark:bg-gray-800 hover:bg-gray-50 dark:hover:bg-gray-700 focus:outline-none disabled:opacity-50 transition-colors">
                    <span x-show="!submitting || actionVal !== 'draft'">Simpan sebagai Draf</span>
                    <span x-show="submitting && actionVal === 'draft'" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-gray-500" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Menyimpan...
                    </span>
                </button>
                
                <button type="button" @click="submitForm('submit')" x-bind:disabled="submitting" 
                    class="inline-flex justify-center py-2.5 px-6 border border-transparent shadow-sm text-sm font-medium rounded-md text-white bg-gradient-to-r from-indigo-600 to-purple-600 hover:from-indigo-700 hover:to-purple-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 disabled:opacity-50 transition-all">
                    <span x-show="!submitting || actionVal !== 'submit'">Kirim Naskah ke Kurator</span>
                    <span x-show="submitting && actionVal === 'submit'" class="flex items-center gap-2">
                        <svg class="animate-spin h-4 w-4 text-white" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24"><circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle><path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path></svg>
                        Mengirim Naskah...
                    </span>
                </button>
            </div>
        </form>
    </div>
</x-author-layout>
