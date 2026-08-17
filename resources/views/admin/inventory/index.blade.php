@php
    $movementLabels = [
        'initial' => 'Saldo awal',
        'adjustment' => 'Penyesuaian',
        'receipt' => 'Penerimaan',
        'sale' => 'Penjualan',
        'reservation' => 'Reservasi',
        'release' => 'Release',
        'transfer' => 'Transfer',
    ];
@endphp

<x-layouts.admin :title="'Inventori'" :header="'Inventori & Stok'">
    <div
        x-data="{
            adjust: { id: null, label: '' },
            transfer: { id: null, label: '', warehouse_id: '' },
            openAdjust(row) {
                this.adjust = { id: row.id, label: row.label, quantity: '', notes: '' };
                $dispatch('open-modal', 'inventory-adjust');
            },
            openTransfer(row) {
                this.transfer = { id: row.id, label: row.label, to_warehouse_id: '', quantity: '', notes: '' };
                $dispatch('open-modal', 'inventory-transfer');
            },
            adjustAction() { return `{{ url('/admin/inventory') }}/${this.adjust.id}/adjust`; },
            transferAction() { return `{{ url('/admin/inventory') }}/${this.transfer.id}/transfer`; }
        }"
        @if(session('open_modal') === 'inventory-adjust') x-init="$dispatch('open-modal', 'inventory-adjust')" @endif
        @if(session('open_modal') === 'inventory-transfer') x-init="$dispatch('open-modal', 'inventory-transfer')" @endif
    >
        <x-ui.page-header
            title="Inventori & Stok"
            description="Kelola stok multi-gudang, transfer antar lokasi, dan audit mutasi lengkap."
        >
            @if ($lowStockCount > 0)
                <span class="rounded-full border border-amber-400/30 bg-amber-400/10 px-3 py-1 text-xs text-amber-200">{{ $lowStockCount }} SKU menipis</span>
            @endif
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif

        <div class="mb-5 flex gap-2">
            <a href="{{ route('admin.inventory.index', array_merge(request()->except('tab'), ['tab' => 'stock'])) }}"
               @class(['rounded-xl px-4 py-2 text-sm font-medium transition', 'bg-white/10 text-white' => $tab === 'stock', 'text-slate-400 hover:bg-white/5' => $tab !== 'stock'])">
                Stok per Gudang
            </a>
            <a href="{{ route('admin.inventory.index', array_merge(request()->except('tab'), ['tab' => 'movements'])) }}"
               @class(['rounded-xl px-4 py-2 text-sm font-medium transition', 'bg-white/10 text-white' => $tab === 'movements', 'text-slate-400 hover:bg-white/5' => $tab !== 'movements'])">
                Audit Mutasi
            </a>
        </div>

        <x-ui.card padding="p-4" class="mb-5">
            <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-5">
                <input type="hidden" name="tab" value="{{ $tab }}">
                <input name="search" value="{{ request('search') }}" placeholder="Cari SKU, produk..." class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                <select name="warehouse_id" class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                    <option value="">Semua gudang</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}" @selected(request('warehouse_id') == $warehouse->id)>{{ $warehouse->code }}</option>
                    @endforeach
                </select>
                @if ($tab === 'stock')
                    <select name="product_type" class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                        <option value="">Semua tipe</option>
                        <option value="apparel" @selected(request('product_type') === 'apparel')>Apparel</option>
                        <option value="spare_part" @selected(request('product_type') === 'spare_part')>Spare part</option>
                        <option value="custom_service" @selected(request('product_type') === 'custom_service')>Custom</option>
                    </select>
                    <label class="flex items-center gap-2 rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-slate-300">
                        <input type="checkbox" name="low_stock" value="1" @checked(request()->boolean('low_stock')) class="rounded border-white/20">
                        Hanya stok menipis
                    </label>
                @else
                    <select name="type" class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                        <option value="">Semua tipe mutasi</option>
                        @foreach ($movementTypes as $type)
                            <option value="{{ $type }}" @selected(request('type') === $type)>{{ $movementLabels[$type] ?? $type }}</option>
                        @endforeach
                    </select>
                    <input name="date_from" type="date" value="{{ request('date_from') }}" class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                    <input name="date_to" type="date" value="{{ request('date_to') }}" class="erp-field rounded-xl border border-white/10 bg-slate-950/80 px-3 py-2 text-sm text-white">
                @endif
                <x-ui.button type="submit" variant="secondary">Terapkan Filter</x-ui.button>
            </form>
        </x-ui.card>

        @if ($tab === 'stock' && $inventories)
            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[900px] text-left text-sm">
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
                                @php $lowStock = $inventory->minimum_stock > 0 && $inventory->stock_on_hand <= $inventory->minimum_stock; @endphp
                                <tr class="{{ $lowStock ? 'bg-amber-500/5' : '' }}">
                                    <td class="px-5 py-4 font-mono text-cyan-300">{{ $inventory->product->sku }}</td>
                                    <td class="px-5 py-4">
                                        <p class="font-medium text-white">{{ $inventory->product->name }}</p>
                                        <p class="text-xs text-slate-500">{{ $inventory->product->brand?->name }}</p>
                                    </td>
                                    <td class="px-5 py-4 text-slate-400">{{ $inventory->warehouse->code }}</td>
                                    <td class="px-5 py-4 {{ $lowStock ? 'font-semibold text-amber-300' : 'text-white' }}">{{ $inventory->available_stock }}</td>
                                    <td class="px-5 py-4 text-slate-300">{{ $inventory->stock_on_hand }}</td>
                                    <td class="px-5 py-4 text-violet-300">{{ $inventory->reserved_stock }}</td>
                                    <td class="px-5 py-4 text-slate-400">{{ $inventory->minimum_stock }}</td>
                                    <td class="px-5 py-4 text-right">
                                        <div class="flex justify-end gap-1">
                                            @can('adjust', $inventory)
                                                <x-ui.button type="button" variant="ghost" size="sm" @click="openAdjust({{ Js::from(['id' => $inventory->id, 'label' => $inventory->product->sku.' — '.$inventory->warehouse->name]) }})">Sesuaikan</x-ui.button>
                                                <x-ui.button type="button" variant="ghost" size="sm" @click="openTransfer({{ Js::from(['id' => $inventory->id, 'label' => $inventory->product->sku.' — '.$inventory->warehouse->name]) }})">Transfer</x-ui.button>
                                            @endcan
                                        </div>
                                    </td>
                                </tr>
                            @empty
                                <tr><td colspan="8" class="px-5 py-12 text-center text-slate-400">Tidak ada data stok.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($inventories->hasPages())
                    <div class="border-t border-white/10 px-5 py-4">{{ $inventories->links() }}</div>
                @endif
            </x-ui.card>
        @endif

        @if ($tab === 'movements' && $movements)
            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="overflow-x-auto">
                    <table class="w-full min-w-[800px] text-left text-sm">
                        <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                            <tr>
                                <th class="px-5 py-3">Waktu</th>
                                <th class="px-5 py-3">Tipe</th>
                                <th class="px-5 py-3">Produk</th>
                                <th class="px-5 py-3">Gudang</th>
                                <th class="px-5 py-3">Qty</th>
                                <th class="px-5 py-3">Stok</th>
                                <th class="px-5 py-3">Catatan</th>
                            </tr>
                        </thead>
                        <tbody class="divide-y divide-white/5">
                            @forelse ($movements as $movement)
                                <tr>
                                    <td class="px-5 py-4 text-slate-400">{{ $movement->created_at->format('d/m H:i') }}</td>
                                    <td class="px-5 py-4"><span class="rounded-full bg-white/10 px-2 py-0.5 text-xs">{{ $movementLabels[$movement->type] ?? $movement->type }}</span></td>
                                    <td class="px-5 py-4 text-white">{{ $movement->product?->sku }}</td>
                                    <td class="px-5 py-4 text-slate-400">{{ $movement->warehouse?->code }}</td>
                                    <td class="px-5 py-4 {{ $movement->quantity < 0 ? 'text-rose-300' : 'text-emerald-300' }}">{{ $movement->quantity > 0 ? '+' : '' }}{{ $movement->quantity }}</td>
                                    <td class="px-5 py-4 text-slate-400">{{ $movement->stock_before }} → {{ $movement->stock_after }}</td>
                                    <td class="px-5 py-4 max-w-xs truncate text-slate-500" title="{{ $movement->notes }}">{{ $movement->notes }}</td>
                                </tr>
                            @empty
                                <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada mutasi.</td></tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if ($movements->hasPages())
                    <div class="border-t border-white/10 px-5 py-4">{{ $movements->links() }}</div>
                @endif
            </x-ui.card>
        @endif

        <x-ui.modal name="inventory-adjust" title="Penyesuaian Stok">
            <form method="POST" :action="adjustAction()" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm text-slate-200" x-text="adjust.label"></p>
                <input name="quantity" type="number" required placeholder="+/- qty" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                <textarea name="notes" rows="3" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white"></textarea>
                <div class="flex justify-end gap-2"><x-ui.button type="submit">Simpan</x-ui.button></div>
            </form>
        </x-ui.modal>

        <x-ui.modal name="inventory-transfer" title="Transfer Antar Gudang">
            <form method="POST" :action="transferAction()" class="space-y-4">
                @csrf
                @method('PATCH')
                <p class="rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm" x-text="transfer.label"></p>
                <select name="to_warehouse_id" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                    <option value="">Gudang tujuan</option>
                    @foreach ($warehouses as $warehouse)
                        <option value="{{ $warehouse->id }}">{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                    @endforeach
                </select>
                <input name="quantity" type="number" min="1" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                <textarea name="notes" rows="2" required placeholder="Alasan transfer" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white"></textarea>
                <x-ui.button type="submit">Proses Transfer</x-ui.button>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
