<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-text-primary">Manajemen Metode Pembayaran</h2>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    <div class="flex flex-col xl:flex-row gap-6">

        <!-- Left: List of Methods -->
        <div class="w-full xl:w-2/3 space-y-6">
            <h3 class="font-bold text-lg text-text-primary mb-2">Metode Tersedia</h3>
            @forelse($methods as $method)
                <div class="bg-surface rounded-xl border border-border p-6 shadow-sm transition-all relative overflow-hidden" x-data="{ editing: false }">
                    <!-- Status accent -->
                    <div class="absolute left-0 top-0 bottom-0 w-1 {{ $method->is_active ? 'bg-green-500' : 'bg-text-muted' }}"></div>

                    <div x-show="!editing">
                        <div class="flex justify-between items-start pl-2">
                            <div class="flex items-center gap-3">
                                <div>
                                    <h4 class="font-bold text-lg text-text-primary flex items-center gap-2">
                                        {{ $method->name }}
                                        <x-badge variant="{{ $method->is_active ? 'success' : 'neutral' }}">
                                            {{ $method->is_active ? 'Aktif' : 'Nonaktif' }}
                                        </x-badge>
                                    </h4>
                                    <p class="text-sm text-text-secondary mt-1 uppercase tracking-wider font-semibold">{{ str_replace('_', ' ', $method->type) }}</p>
                                </div>
                            </div>
                            <x-button type="button" @click="editing = true" variant="secondary" size="sm">
                                <svg class="w-4 h-4 mr-1" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                Edit
                            </x-button>
                        </div>

                        <div class="mt-4 pl-2 grid grid-cols-1 md:grid-cols-2 gap-4">
                            @if($method->account_name || $method->account_number)
                                <div class="bg-background p-3 rounded-lg border border-border">
                                    <span class="block text-xs text-text-muted mb-1">Informasi Akun</span>
                                    <span class="block font-medium text-text-primary">{{ $method->account_number ?: '-' }}</span>
                                    <span class="block text-sm text-text-secondary">{{ $method->account_name ?: '-' }}</span>
                                </div>
                            @endif

                            @if($method->type === 'qris' || $method->qr_image_path)
                                <div class="bg-background p-3 rounded-lg border border-border flex items-center gap-3">
                                    <div class="w-16 h-16 flex-shrink-0 bg-white border border-gray-200 rounded p-1 flex items-center justify-center">
                                        @if($method->qr_image_path)
                                            <img src="{{ asset('storage/'.$method->qr_image_path) }}" alt="QR Code" class="max-w-full max-h-full object-contain">
                                        @else
                                            <span class="text-[10px] text-gray-400 text-center">QR Belum<br>Diunggah</span>
                                        @endif
                                    </div>
                                    <div>
                                        <span class="block text-xs text-text-muted mb-1">QR Code</span>
                                        <span class="block text-sm font-medium text-text-primary">{{ $method->qr_image_path ? 'Tersedia' : 'Tidak Ada' }}</span>
                                    </div>
                                </div>
                            @endif
                        </div>
                        @if($method->instructions)
                            <div class="mt-4 pl-2">
                                <span class="block text-xs text-text-muted mb-1">Instruksi Pembayaran</span>
                                <p class="text-sm text-text-secondary bg-background p-3 rounded-lg border border-border whitespace-pre-wrap">{{ $method->instructions }}</p>
                            </div>
                        @endif
                    </div>

                    <!-- Edit Form -->
                    <div x-show="editing" x-cloak class="pl-2">
                        <div class="flex justify-between items-center mb-4 border-b border-border pb-2">
                            <h4 class="font-bold text-text-primary">Edit Metode: {{ $method->name }}</h4>
                            <button @click="editing = false" type="button" class="text-text-muted hover:text-text-primary">
                                <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                            </button>
                        </div>
                        <form method="POST" enctype="multipart/form-data" action="{{ route('admin.payment-methods.update', $method) }}" class="space-y-4">
                            @csrf
                            @method('PUT')
                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                                <x-form-field id="type_{{ $method->id }}" name="type" type="select" label="Tipe" :value="$method->type" :options="['bank'=>'Bank', 'e_wallet'=>'E-wallet', 'qris'=>'QRIS', 'other'=>'Lainnya']" required />
                                <x-form-field id="name_{{ $method->id }}" name="name" label="Nama Metode" :value="$method->name" required />
                                <x-form-field id="account_name_{{ $method->id }}" name="account_name" label="Nama Rekening" :value="$method->account_name" />
                                <x-form-field id="account_number_{{ $method->id }}" name="account_number" label="Nomor Rekening/Telepon" :value="$method->account_number" />
                            </div>

                            <x-form-field id="instructions_{{ $method->id }}" name="instructions" type="textarea" label="Instruksi Pembayaran" :value="$method->instructions" />

                            <div class="grid grid-cols-1 md:grid-cols-2 gap-4 bg-background p-4 rounded-lg border border-border">
                                <div>
                                    <label class="block text-sm font-bold text-text-primary mb-2">Perbarui QR Code</label>
                                    <input type="file" name="qr_image" accept="image/*" class="block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-surface-hover file:text-text-primary hover:file:bg-border">
                                </div>
                                <div class="flex items-center">
                                    <label class="flex items-center gap-3 cursor-pointer">
                                        <input type="checkbox" name="is_active" value="1" {{ $method->is_active ? 'checked' : '' }} class="w-5 h-5 rounded border-border text-primary focus:ring-primary">
                                        <span class="font-bold text-text-primary">Metode Aktif</span>
                                    </label>
                                </div>
                            </div>

                            <div class="flex justify-end gap-3 pt-2">
                                <x-button type="button" @click="editing = false" variant="secondary">Batal</x-button>
                                <x-button type="submit" variant="primary">Simpan Perubahan</x-button>
                            </div>
                        </form>
                    </div>
                </div>
            @empty
                <x-empty-state title="Belum ada metode" description="Silakan tambahkan metode pembayaran di samping." />
            @endforelse
        </div>

        <!-- Right: Create New Method -->
        <div class="w-full xl:w-1/3">
            <div class="bg-surface rounded-xl border border-border p-6 sticky top-6">
                <h3 class="font-bold text-lg text-text-primary mb-4 border-b border-border pb-2">Tambah Metode Baru</h3>
                <form method="POST" enctype="multipart/form-data" action="{{ route('admin.payment-methods.store') }}" class="space-y-4">
                    @csrf
                    <x-form-field id="type" name="type" type="select" label="Tipe" :options="['bank'=>'Bank', 'e_wallet'=>'E-wallet', 'qris'=>'QRIS', 'other'=>'Lainnya']" required />
                    <x-form-field id="name" name="name" label="Nama Metode" placeholder="Bank Mandiri, DANA, dll" required />
                    <x-form-field id="account_name" name="account_name" label="Nama Rekening" placeholder="Atas nama..." />
                    <x-form-field id="account_number" name="account_number" label="Nomor Rekening / Telepon" placeholder="123-456-..." />
                    <x-form-field id="instructions" name="instructions" type="textarea" label="Instruksi (Opsional)" />

                    <div class="pt-2">
                        <label class="block text-sm font-bold text-text-primary mb-2">QR Code Image</label>
                        <input type="file" name="qr_image" accept="image/*" class="block w-full text-sm text-text-secondary file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-semibold file:bg-surface-hover file:text-text-primary hover:file:bg-border">
                    </div>

                    <div class="pt-2">
                        <label class="flex items-center gap-3 cursor-pointer">
                            <input type="checkbox" name="is_active" value="1" checked class="w-5 h-5 rounded border-border text-primary focus:ring-primary">
                            <span class="font-bold text-text-primary">Langsung Aktifkan</span>
                        </label>
                    </div>

                    <div class="pt-4 border-t border-border">
                        <x-button type="submit" variant="primary" class="w-full justify-center">Simpan Metode Baru</x-button>
                    </div>
                </form>
            </div>
        </div>

    </div>
</x-admin-layout>
