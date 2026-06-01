<x-layouts.admin :title="'Inventori'" :header="'Inventori & Stok'">
    <div
        x-data="{
            adjust: { id: null, label: '', quantity: '', notes: '' },
            openAdjust(row) {
                this.adjust = { id: row.id, label: row.label, quantity: '', notes: '' };
                $dispatch('open-modal', 'inventory-adjust');
            },
            formAction() {
                return `{{ url('/admin/inventory') }}/${this.adjust.id}/adjust`;
            }
        }"
        @if(session('open_modal') === 'inventory-adjust') x-init="$dispatch('open-modal', 'inventory-adjust')" @endif
    >
        <x-ui.page-header
            title="Inventori & Stok"
            description="Pantau stok per produk dan gudang. Semua penyesuaian dicatat sebagai mutasi."
        />

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif
        @if ($errors->any() && session('open_modal') === 'inventory-adjust')
            <x-ui.alert type="error" class="mb-5">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-white/10 p-4">
                <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <input name="search" value="{{ request('search') }}" placeholder="Cari SKU, produk, atau gudang..." class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 sm:max-w-md">
                    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3">SKU</th>
                            <th class="px-5 py-3">Produk</th>
                            <th class="px-5 py-3">Gudang</th>
                            <th class="px-5 py-3">Tersedia</th>
                            <th class="px-5 py-3">Fisik</th>
                            <th class="px-5 py-3">Reserved</th>
                            <th class="px-5 py-3">Min</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($inventories as $inventory)
                            @php
                                $lowStock = $inventory->stock_on_hand <= $inventory->minimum_stock;
                            @endphp
                            <tr class="hover:bg-white/[0.02] {{ $lowStock ? 'bg-amber-500/5' : '' }}">
                                <td class="px-5 py-4 font-mono text-cyan-300">{{ $inventory->product->sku }}</td>
                                <td class="px-5 py-4 font-medium text-white">{{ $inventory->product->name }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $inventory->warehouse->code }}</td>
                                <td class="px-5 py-4 {{ $lowStock ? 'text-amber-300 font-semibold' : 'text-white' }}">{{ $inventory->available_stock }}</td>
                                <td class="px-5 py-4 text-slate-300">{{ $inventory->stock_on_hand }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $inventory->reserved_stock }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $inventory->minimum_stock }}</td>
                                <td class="px-5 py-4 text-right">
                                    @can('adjust', $inventory)
                                        <x-ui.button
                                            type="button"
                                            variant="secondary"
                                            size="sm"
                                            @click="openAdjust({{ Js::from([
                                                'id' => $inventory->id,
                                                'label' => $inventory->product->sku.' — '.$inventory->warehouse->name,
                                            ]) }})"
                                        >Sesuaikan</x-ui.button>
                                    @endcan
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="8" class="px-5 py-12 text-center text-slate-400">Belum ada stok inventori. Tambahkan gudang dan produk terlebih dahulu.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($inventories->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $inventories->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.modal name="inventory-adjust" title="Penyesuaian Stok" subtitle="Masukkan qty positif/negatif dan alasan penyesuaian.">
            <form method="POST" :action="formAction()" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200" x-text="adjust.label"></p>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Perubahan Qty (+/-)</label>
                    <input name="quantity" type="number" x-model="adjust.quantity" required placeholder="Contoh: 10 atau -5" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div>
                    <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Alasan</label>
                    <textarea name="notes" x-model="adjust.notes" rows="3" required placeholder="Contoh: Koreksi stok opname" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'inventory-adjust')">Batal</x-ui.button>
                    <x-ui.button type="submit">Simpan Penyesuaian</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
