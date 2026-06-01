@php
    $productTypes = [
        'apparel' => 'Apparel',
        'spare_part' => 'Spare part',
        'custom_service' => 'Layanan custom',
    ];
@endphp

<x-layouts.admin :title="'Produk'" :header="'Master Produk'">
    <div
        x-data="{
            mode: 'create',
            item: { id: null, sku: '', name: '', product_type: 'apparel', price: 0, is_active: true, warehouse_id: '', initial_stock: 0, minimum_stock: 0 },
            openCreate() {
                this.mode = 'create';
                this.item = { id: null, sku: '', name: '', product_type: 'apparel', price: 0, is_active: true, warehouse_id: '', initial_stock: 0, minimum_stock: 0 };
                $dispatch('open-modal', 'product-form');
            },
            openEdit(product) {
                this.mode = 'edit';
                this.item = { ...product, is_active: Boolean(product.is_active) };
                $dispatch('open-modal', 'product-form');
            },
            formAction() {
                return this.mode === 'create'
                    ? '{{ route('admin.products.store') }}'
                    : `{{ url('/admin/products') }}/${this.item.id}`;
            }
        }"
        @if(session('open_modal') === 'product-form') x-init="$dispatch('open-modal', 'product-form')" @endif
    >
        <x-ui.page-header
            title="Master Produk"
            description="Kelola SKU, harga jual, dan status ketersediaan produk."
        >
            @can('create', App\Models\Product::class)
                <x-ui.button type="button" @click="openCreate()">+ Tambah Produk</x-ui.button>
            @endcan
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
        @endif
        @if ($errors->any() && session('open_modal') === 'product-form')
            <x-ui.alert type="error" class="mb-5">{{ $errors->first() }}</x-ui.alert>
        @endif

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="border-b border-white/10 p-4">
                <form method="GET" class="flex flex-col gap-2 sm:flex-row">
                    <input name="search" value="{{ request('search') }}" placeholder="Cari SKU atau nama produk..." class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 sm:max-w-md">
                    <x-ui.button type="submit" variant="secondary">Cari</x-ui.button>
                </form>
            </div>

            <div class="overflow-x-auto">
                <table class="w-full min-w-[720px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3">SKU</th>
                            <th class="px-5 py-3">Nama</th>
                            <th class="px-5 py-3">Tipe</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Stok</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($products as $product)
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-5 py-4 font-mono text-cyan-300">{{ $product->sku }}</td>
                                <td class="px-5 py-4 font-medium text-white">{{ $product->name }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $productTypes[$product->product_type] ?? $product->product_type }}</td>
                                <td class="px-5 py-4 text-white">Rp {{ number_format($product->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-slate-300">{{ (int) ($product->total_stock ?? 0) }}</td>
                                <td class="px-5 py-4">
                                    <span class="rounded-full px-2.5 py-1 text-xs {{ $product->is_active ? 'bg-emerald-400/10 text-emerald-200' : 'bg-slate-500/20 text-slate-400' }}">
                                        {{ $product->is_active ? 'Aktif' : 'Nonaktif' }}
                                    </span>
                                </td>
                                <td class="px-5 py-4">
                                    <div class="flex justify-end gap-2">
                                        @can('update', $product)
                                            <x-ui.button type="button" variant="ghost" size="sm" @click="openEdit({{ Js::from([
                                                'id' => $product->id,
                                                'sku' => $product->sku,
                                                'name' => $product->name,
                                                'product_type' => $product->product_type,
                                                'price' => (float) $product->price,
                                                'is_active' => $product->is_active,
                                            ]) }})">Edit</x-ui.button>
                                        @endcan
                                        @can('delete', $product)
                                            <form method="POST" action="{{ route('admin.products.destroy', $product) }}" onsubmit="return confirm('Hapus atau nonaktifkan produk ini?')">
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
                                <td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada produk.</td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($products->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $products->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.modal name="product-form" title="Produk" subtitle="Data produk dan saldo awal (hanya saat tambah)." maxWidth="xl">
            <form method="POST" :action="formAction()" class="space-y-4">
                @csrf
                <template x-if="mode === 'edit'">
                    <input type="hidden" name="_method" value="PUT">
                </template>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">SKU</label>
                        <input name="sku" x-model="item.sku" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Nama Produk</label>
                        <input name="name" x-model="item.name" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>

                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Tipe</label>
                        <select name="product_type" x-model="item.product_type" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                            @foreach ($productTypes as $value => $label)
                                <option value="{{ $value }}">{{ $label }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs font-medium uppercase tracking-wider text-slate-400">Harga (Rp)</label>
                        <input name="price" type="number" min="0" step="1" x-model="item.price" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>

                <template x-if="mode === 'create'">
                    <div class="grid gap-4 rounded-xl border border-white/10 bg-white/5 p-4 sm:grid-cols-3">
                        <div class="sm:col-span-3 text-xs font-medium uppercase tracking-wider text-slate-400">Saldo Awal Inventori</div>
                        <div class="sm:col-span-3">
                            <label class="mb-1.5 block text-xs text-slate-400">Gudang</label>
                            <select name="warehouse_id" x-model="item.warehouse_id" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                                <option value="">Pilih gudang</option>
                                @foreach ($warehouses as $warehouse)
                                    <option value="{{ $warehouse->id }}">{{ $warehouse->code }} — {{ $warehouse->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs text-slate-400">Stok awal</label>
                            <input name="initial_stock" type="number" min="0" x-model="item.initial_stock" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                        </div>
                        <div>
                            <label class="mb-1.5 block text-xs text-slate-400">Stok minimum</label>
                            <input name="minimum_stock" type="number" min="0" x-model="item.minimum_stock" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                        </div>
                    </div>
                </template>

                <div class="flex items-center gap-2">
                    <input type="hidden" name="is_active" :value="item.is_active ? 1 : 0">
                    <input type="checkbox" x-model="item.is_active" class="rounded border-white/20 bg-slate-950 text-cyan-400">
                    <span class="text-sm text-slate-300">Produk aktif</span>
                </div>

                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'product-form')">Batal</x-ui.button>
                    <x-ui.button type="submit" x-text="mode === 'create' ? 'Simpan Produk' : 'Perbarui Produk'"></x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
