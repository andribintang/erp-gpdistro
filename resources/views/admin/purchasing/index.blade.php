@php
    $poStatuses = [
        'draft' => 'Draft',
        'submitted' => 'Diajukan',
        'approved' => 'Disetujui',
        'received' => 'Diterima',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

<x-layouts.admin :title="'Pembelian'" :header="'Pembelian & Supplier'">
    <div
        x-data="{
            supplierMode: 'create',
            supplier: { id: null, name: '', email: '', phone: '', address: '' },
            openSupplierCreate() {
                this.supplierMode = 'create';
                this.supplier = { id: null, name: '', email: '', phone: '', address: '' };
                $dispatch('open-modal', 'supplier-form');
            },
            openSupplierEdit(s) {
                this.supplierMode = 'edit';
                this.supplier = { ...s };
                $dispatch('open-modal', 'supplier-form');
            },
            supplierFormAction() {
                return this.supplierMode === 'create'
                    ? '{{ route('admin.suppliers.store') }}'
                    : `{{ url('/admin/suppliers') }}/${this.supplier.id}`;
            }
        }"
        @if(session('open_modal') === 'supplier-form') x-init="$dispatch('open-modal', 'supplier-form')" @endif
        @if(session('open_modal') === 'po-form') x-init="$dispatch('open-modal', 'po-form')" @endif
    >
        <x-ui.page-header
            title="Pembelian & Supplier"
            description="Kelola supplier, buat purchase order, setujui, dan posting penerimaan ke inventori."
        >
            <x-ui.button type="button" variant="secondary" @click="openSupplierCreate()">+ Supplier</x-ui.button>
            @can('create', App\Models\PurchaseOrder::class)
                <x-ui.button type="button" @click="$dispatch('open-modal', 'po-form')">+ Purchase Order</x-ui.button>
            @endcan
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card padding="p-5" class="mb-6">
            <h3 class="text-sm font-semibold text-white">Supplier Terdaftar</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse ($suppliers as $supplier)
                    <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm">
                        <span class="text-slate-200">{{ $supplier->name }}</span>
                        <button type="button" class="text-cyan-300 hover:text-cyan-200" @click="openSupplierEdit({{ Js::from($supplier->only(['id','name','email','phone','address'])) }})">Edit</button>
                        @can('delete', $supplier)
                            <form method="POST" action="{{ route('admin.suppliers.destroy', $supplier) }}" class="inline" onsubmit="return confirm('Hapus supplier?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-300 hover:text-rose-200">Hapus</button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada supplier.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-white/10 p-4">
                <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <input name="search" value="{{ request('search') }}" placeholder="Cari nomor PO atau supplier..." class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white sm:max-w-md">
                    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                </form>
            </div>

            <div class="divide-y divide-white/5">
                @forelse ($purchaseOrders as $purchaseOrder)
                    <div class="p-5">
                        <div class="flex flex-wrap items-start justify-between gap-4">
                            <div>
                                <p class="font-mono text-xs text-cyan-300">{{ $purchaseOrder->purchase_number }}</p>
                                <h3 class="mt-1 text-lg font-semibold text-white">{{ $purchaseOrder->supplier->name }}</h3>
                                <p class="mt-1 text-sm text-slate-400">{{ $purchaseOrder->items->count() }} item · {{ $purchaseOrder->order_date->format('d M Y') }}</p>
                            </div>
                            <div class="text-right">
                                <span class="rounded-full bg-amber-400/10 px-3 py-1 text-xs text-amber-200">
                                    {{ $poStatuses[$purchaseOrder->status] ?? $purchaseOrder->status }}
                                </span>
                                <p class="mt-2 text-sm font-semibold text-white">Rp {{ number_format($purchaseOrder->grand_total, 0, ',', '.') }}</p>
                            </div>
                        </div>

                        <ul class="mt-3 space-y-1 text-xs text-slate-400">
                            @foreach ($purchaseOrder->items as $item)
                                <li>{{ $item->product->sku }} — {{ $item->product->name }}: {{ $item->received_qty }}/{{ $item->qty }} diterima</li>
                            @endforeach
                        </ul>

                        @can('approve', $purchaseOrder)
                            @if (in_array($purchaseOrder->status, ['draft', 'submitted'], true))
                                <form method="POST" action="{{ route('admin.purchasing.approve', $purchaseOrder) }}" class="mt-4">
                                    @csrf
                                    @method('PATCH')
                                    <x-ui.button type="submit" variant="secondary" size="sm">Setujui PO</x-ui.button>
                                </form>
                            @endif
                        @endcan

                        @if ($purchaseOrder->status === 'approved')
                            <form method="POST" action="{{ route('admin.purchasing.receive', $purchaseOrder) }}" class="mt-4 space-y-3 rounded-xl border border-white/10 bg-white/5 p-4">
                                @csrf
                                @method('PATCH')
                                <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Penerimaan barang</p>
                                <select name="warehouse_id" required class="erp-field w-full max-w-xs rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                                    <option value="">Pilih gudang</option>
                                    @foreach ($warehouses as $warehouse)
                                        <option value="{{ $warehouse->id }}">{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                                    @endforeach
                                </select>
                                <div class="grid gap-2 sm:grid-cols-2">
                                    @foreach ($purchaseOrder->items->where('remaining_qty', '>', 0) as $item)
                                        <div class="rounded-lg border border-white/10 p-3">
                                            <input type="hidden" name="items[{{ $loop->index }}][purchase_order_item_id]" value="{{ $item->id }}">
                                            <p class="text-xs text-slate-300">{{ $item->product->sku }} (sisa {{ $item->remaining_qty }})</p>
                                            <input name="items[{{ $loop->index }}][quantity]" type="number" min="1" max="{{ $item->remaining_qty }}" value="{{ $item->remaining_qty }}" required class="erp-field mt-2 w-full rounded-lg border border-white/10 bg-slate-950 px-2 py-1.5 text-sm text-white">
                                        </div>
                                    @endforeach
                                </div>
                                <textarea name="notes" rows="2" placeholder="Catatan penerimaan" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white"></textarea>
                                <x-ui.button type="submit" size="sm">Terima & Posting Stok</x-ui.button>
                            </form>
                        @endif
                    </div>
                @empty
                    <p class="px-5 py-12 text-center text-slate-400">Belum ada purchase order.</p>
                @endforelse
            </div>
            @if ($purchaseOrders->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $purchaseOrders->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.modal name="supplier-form" title="Supplier" subtitle="Data pemasok untuk proses pembelian.">
            <form method="POST" :action="supplierFormAction()" class="space-y-4">
                @csrf
                <template x-if="supplierMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Nama</label>
                    <input name="name" x-model="supplier.name" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Telepon</label>
                        <input name="phone" x-model="supplier.phone" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Email</label>
                        <input name="email" type="email" x-model="supplier.email" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Alamat</label>
                    <textarea name="address" x-model="supplier.address" rows="2" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'supplier-form')">Batal</x-ui.button>
                    <x-ui.button type="submit" x-text="supplierMode === 'create' ? 'Simpan Supplier' : 'Perbarui Supplier'"></x-ui.button>
                </div>
            </form>
        </x-ui.modal>

        <x-ui.modal name="po-form" title="Buat Purchase Order" subtitle="Versi awal: satu item per PO." maxWidth="xl">
            <form method="POST" action="{{ route('admin.purchasing.store') }}" class="space-y-4">
                @csrf
                @if ($errors->any() && session('open_modal') === 'po-form')
                    <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
                @endif
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Supplier</label>
                    <select name="supplier_id" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                        <option value="">Pilih supplier</option>
                        @foreach ($suppliers as $supplier)
                            <option value="{{ $supplier->id }}" @selected(old('supplier_id') == $supplier->id)>{{ $supplier->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Tanggal PO</label>
                        <input name="order_date" type="date" value="{{ old('order_date', now()->toDateString()) }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Estimasi tiba</label>
                        <input name="expected_date" type="date" value="{{ old('expected_date') }}" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Produk</label>
                    <select name="items[0][product_id]" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                        <option value="">Pilih produk</option>
                        @foreach ($products as $product)
                            <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Qty</label>
                        <input name="items[0][qty]" type="number" min="1" value="{{ old('items.0.qty', 1) }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Harga satuan</label>
                        <input name="items[0][unit_price]" type="number" min="0" value="{{ old('items.0.unit_price') }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Catatan</label>
                    <textarea name="notes" rows="2" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">{{ old('notes') }}</textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'po-form')">Batal</x-ui.button>
                    <x-ui.button type="submit">Buat PO</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
