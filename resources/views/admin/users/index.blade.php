<x-admin-layout>
    <x-slot name="header">
        <h2 class="font-bold text-2xl text-text-primary">
            {{ __('Manajemen Pengguna') }}
        </h2>
    </x-slot>

    @if (session('success'))
        <div class="mb-6 bg-green-50 border border-green-200 text-green-700 p-4 rounded-xl flex items-center gap-3">
            <svg class="w-5 h-5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
            <p class="font-medium">{{ session('success') }}</p>
        </div>
    @endif

    @if ($errors->any())
        <div class="mb-6 bg-red-50 border border-red-200 text-red-700 p-4 rounded-xl flex items-start gap-3">
            <svg class="w-5 h-5 mt-0.5" fill="none" viewBox="0 0 24 24" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
            <ul class="list-disc list-inside">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <div class="bg-surface rounded-xl border border-border p-5 mb-6 shadow-sm">
        <form method="GET" action="{{ route('admin.users.index') }}" class="flex flex-col md:flex-row gap-4 items-end">
            <div class="w-full md:flex-1">
                <label for="keyword" class="block text-sm font-bold text-text-primary mb-1">Pencarian</label>
                <input type="text" name="keyword" id="keyword" value="{{ request('keyword') }}" placeholder="Nama atau email..." class="w-full rounded-lg border-border bg-background px-3 py-2 text-sm focus:ring-primary focus:border-primary transition-colors">
            </div>
            <div class="w-full md:w-1/4 xl:w-1/5">
                <label for="status" class="block text-sm font-bold text-text-primary mb-1">Status</label>
                <select name="status" id="status" class="w-full rounded-lg border-border bg-background px-3 py-2 text-sm focus:ring-primary focus:border-primary transition-colors">
                    <option value="all" {{ request('status') === 'all' ? 'selected' : '' }}>Semua Status</option>
                    <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Aktif</option>
                    <option value="suspended" {{ request('status') === 'suspended' ? 'selected' : '' }}>Ditangguhkan</option>
                </select>
            </div>
            <div class="w-full md:w-1/4 xl:w-1/5">
                <label for="role" class="block text-sm font-bold text-text-primary mb-1">Peran</label>
                <select name="role" id="role" class="w-full rounded-lg border-border bg-background px-3 py-2 text-sm focus:ring-primary focus:border-primary transition-colors">
                    <option value="all" {{ request('role') === 'all' ? 'selected' : '' }}>Semua Peran</option>
                    <option value="customer" {{ request('role') === 'customer' ? 'selected' : '' }}>Customer</option>
                    <option value="author" {{ request('role') === 'author' ? 'selected' : '' }}>Author</option>
                    <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                </select>
            </div>
            <div class="w-full md:w-auto flex items-center gap-2">
                <x-button type="submit" variant="primary">Filter</x-button>
                <a href="{{ route('admin.users.index') }}" class="px-4 py-2 text-sm text-text-secondary hover:text-text-primary transition-colors font-medium">Reset</a>
            </div>
        </form>
    </div>

    <x-table-wrapper>
        <thead class="bg-surface-hover border-b border-border text-xs uppercase tracking-wider text-text-muted">
            <tr>
                <th class="px-6 py-4 text-left font-medium">Pengguna</th>
                <th class="px-6 py-4 text-left font-medium">Peran</th>
                <th class="px-6 py-4 text-left font-medium">Status</th>
                <th class="px-6 py-4 text-left font-medium">Metrik (Pesanan & Lisensi)</th>
                <th class="px-6 py-4 text-right font-medium">Aksi</th>
            </tr>
        </thead>
        <tbody class="divide-y divide-border">
            @forelse ($users as $user)
                <tr class="hover:bg-surface-hover/30 transition-colors">
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex items-center gap-4">
                            <div class="flex-shrink-0 h-10 w-10 rounded-full bg-primary/10 flex items-center justify-center border border-primary/20">
                                <span class="text-primary font-bold text-lg">{{ substr($user->name, 0, 1) }}</span>
                            </div>
                            <div>
                                <div class="text-sm font-bold text-text-primary">{{ $user->name }}</div>
                                <div class="text-xs text-text-muted">{{ $user->email }}</div>
                            </div>
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        <div class="flex flex-col gap-1">
                            @if ($user->is_admin)
                                <x-badge variant="neutral">Admin</x-badge>
                            @else
                                <x-badge variant="neutral">Customer</x-badge>
                            @endif
                            @if ($user->is_author)
                                <x-badge variant="success">Author</x-badge>
                            @endif
                        </div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap">
                        @if ($user->is_active)
                            <x-badge variant="success">Aktif</x-badge>
                        @else
                            <x-badge variant="error">Ditangguhkan</x-badge>
                        @endif
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-sm text-text-secondary">
                        <div class="flex gap-4">
                            <div><strong class="text-text-primary">{{ (int)$user->completed_orders_count }}</strong> Pesanan</div>
                            <div><strong class="text-text-primary">{{ (int)$user->active_licenses_count }}</strong> Lisensi</div>
                        </div>
                        <div class="text-xs text-text-muted mt-1">Total Belanja: Rp{{ number_format((float)$user->total_spent, 0, ',', '.') }}</div>
                    </td>
                    <td class="px-6 py-4 whitespace-nowrap text-right text-sm font-medium">
                        <div class="flex justify-end gap-3 items-center">
                            <a href="{{ route('admin.users.show', $user->id) }}" class="text-primary hover:underline font-semibold">Lihat Detail</a>

                            @if ($user->id !== auth()->id())
                                <span class="text-border">|</span>
                                <form action="{{ route('admin.users.toggle-status', $user->id) }}" method="POST" class="inline" onsubmit="return confirm('Apakah Anda yakin ingin {{ $user->is_active ? 'menangguhkan' : 'mengaktifkan' }} pengguna ini?');">
                                    @csrf
                                    @method('PATCH')
                                    <button type="submit" class="font-semibold transition-colors {{ $user->is_active ? 'text-red-500 hover:text-red-700' : 'text-green-600 hover:text-green-700' }}">
                                        {{ $user->is_active ? 'Tangguhkan' : 'Aktifkan' }}
                                    </button>
                                </form>
                            @endif
                        </div>
                    </td>
                </tr>
            @empty
                <tr>
                    <td colspan="5" class="px-6 py-8 text-center text-text-muted">
                        Tidak ada data pengguna yang ditemukan.
                    </td>
                </tr>
            @endforelse
        </tbody>
    </x-table-wrapper>

    <div class="mt-6">
        {{ $users->links() }}
    </div>
</x-admin-layout>
