<x-layouts.admin :title="'Gudang'" :header="'Master Gudang'">
    <div
        x-data="{
            mode: 'create',
            item: { id: null, code: '', name: '', city: '', address: '', is_active: true },
            openCreate() {
                this.mode = 'create';
                this.item = { id: null, code: '', name: '', city: '', address: '', is_active: true };
                $dispatch('open-modal', 'warehouse-form');
            },
            openEdit(warehouse) {
                this.mode = 'edit';
                this.item = { ...warehouse, is_active: Boolean(warehouse.is_active) };
                $dispatch('open-modal', 'warehouse-form');
            },
            formAction() {
                return this.mode === 'create'
                    ? '{{ route('admin.warehouses.store') }}'
                    : `{{ url('/admin/warehouses') }}/${this.item.id}`;
            }
        }"
        @if(session('open_modal') === 'warehouse-form') x-init="$dispatch('open-modal', 'warehouse-form')" @endif
    >
        <x-ui.page-header
            title="Master Gudang"
            description="Kelola lokasi penyimpanan, status aktif, dan kapasitas operasional."
        >
            @can('create', App\Models\Warehouse::class)
                <x-ui.button type="button" @click="openCreate()">+ Tambah Gudang</x-ui.button>
            @endcan
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any() && session('open_modal') === 'warehouse-form')
            <x-ui.alert type="error" class="mb-5">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-white/10 p-4">
                <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <input
                        name="search"
                        value="{{ request('search') }}"
                        placeholder="Cari kode, nama, atau kota..."
                        class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 sm:max-w-md"
                    >
                    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[640px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3">Kode</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Kota</th>
                            <th class="px-5 py-3">SKU</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($warehouses as $warehouse)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-5 py-4 font-mono text-cyan-300">{{ $warehouse->code }}</td>
                                <td class="px-5 py-4 font-medium text-white">{{ $warehouse->name }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $warehouse->city ?: '—' }}</td>
                                <td class="px-5 py-4 text-slate-300">{{ $warehouse->inventories_count }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs {{ $warehouse->is_active ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $warehouse->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $warehouse)
                                            <x-ui.button
                                                type="button"
                                                variant="ghost"
                                                size="sm"
                                                @click="openEdit({{ Js::from($warehouse->only(['id', 'code', 'name', 'city', 'address', 'is_active'])) }})"
                                            >Edit</x-ui.button>
                                        @endcan
                                        @can('delete', $warehouse)
                                            <form method="POST" action="{{ route('admin.warehouses.destroy', $warehouse) }}" onsubmit="return confirm('Hapus gudang ini?')">
                                                @csrf
                                                @method('DELETE')
                                                <x-ui.button type="submit" variant="danger" size="sm">Hapus</x-ui.button>
                                            </form>
                                        @endcan
                                    </div>
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-5 py-12 text-center text-slate-400">Belum ada data gudang.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($warehouses->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $warehouses->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.modal name="warehouse-form" :title="'Gudang'" subtitle="Isi data gudang dengan kode yang konsisten." maxWidth="lg">
            <form method="POST" :action="formAction()" class="space-y-4">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Kode</label>
                        <input name="code" x-model="item.code" value="{{ old('code') }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Nama</label>
                        <input name="name" x-model="item.name" value="{{ old('name') }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Kota</label>
                    <input name="city" x-model="item.city" value="{{ old('city') }}" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Alamat</label>
                    <textarea name="address" x-model="item.address" rows="3" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">{{ old('address') }}</textarea>
                </div>
                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" :value="item.is_active ? 1 : 0">
                    <input type="checkbox" x-model="item.is_active" class="rounded border-white/20 bg-slate-950 text-cyan-400 focus:ring-cyan-400/30">
                    <span class="text-sm text-slate-300">Gudang aktif</span>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'warehouse-form')">Batal</x-ui.button>
                    <x-ui.button type="submit" x-text="mode === 'create' ? 'Simpan Gudang' : 'Perbarui Gudang'"></x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
