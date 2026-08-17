@php
    $orderStatuses = [
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
@endphp

<x-layouts.admin :title="'Penjualan'" :header="'Penjualan & Pelanggan'">
    <div
        x-data="{
            lineItems: [{ product_id: '', qty: 1, price: '' }],
            addLine() { this.lineItems.push({ product_id: '', qty: 1, price: '' }); },
            removeLine(i) { if (this.lineItems.length > 1) this.lineItems.splice(i, 1); },
            customerMode: 'create',
            customer: { id: null, name: '', email: '', phone: '', address: '' },
            openCustomerCreate() {
                this.customerMode = 'create';
                this.customer = { id: null, name: '', email: '', phone: '', address: '' };
                $dispatch('open-modal', 'customer-form');
            },
            openCustomerEdit(c) {
                this.customerMode = 'edit';
                this.customer = { ...c };
                $dispatch('open-modal', 'customer-form');
            },
            customerFormAction() {
                return this.customerMode === 'create'
                    ? '{{ route('admin.customers.store') }}'
                    : `{{ url('/admin/customers') }}/${this.customer.id}`;
            }
        }"
        @if(session('open_modal') === 'customer-form') x-init="$dispatch('open-modal', 'customer-form')" @endif
        @if(session('open_modal') === 'order-form') x-init="$dispatch('open-modal', 'order-form')" @endif
    >
        <x-ui.page-header
            title="Penjualan & Pelanggan"
            description="Kelola pelanggan, buat sales order, tandai lunas, proses stok, dan selesaikan pesanan."
        >
            <x-ui.button type="button" variant="secondary" @click="openCustomerCreate()">+ Pelanggan</x-ui.button>
            @can('create', App\Models\Order::class)
                <x-ui.button type="button" @click="$dispatch('open-modal', 'order-form')">+ Sales Order</x-ui.button>
            @endcan
        </x-ui.page-header>

        @if (session('status'))
            <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
        @endif
        @if (session('error'))
            <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
        @endif

        <x-ui.card padding="p-5" class="mb-6">
            <h3 class="text-sm font-semibold text-white">Pelanggan Terdaftar</h3>
            <div class="mt-3 flex flex-wrap gap-2">
                @forelse ($customers as $customer)
                    <div class="inline-flex items-center gap-2 rounded-xl border border-white/10 bg-white/5 px-3 py-2 text-sm">
                        <span class="text-slate-200">{{ $customer->name }}</span>
                        <button type="button" class="text-cyan-300 hover:text-cyan-200" @click="openCustomerEdit({{ Js::from($customer->only(['id','name','email','phone','address'])) }})">Edit</button>
                        @can('delete', $customer)
                            <form method="POST" action="{{ route('admin.customers.destroy', $customer) }}" class="inline" onsubmit="return confirm('Hapus pelanggan?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-rose-300 hover:text-rose-200">Hapus</button>
                            </form>
                        @endcan
                    </div>
                @empty
                    <p class="text-sm text-slate-400">Belum ada pelanggan.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card padding="p-4" class="mb-5">
            <form method="GET" class="grid gap-3 md:grid-cols-2 xl:grid-cols-6">
                <input name="search" value="{{ request('search') }}" placeholder="Cari SO / pelanggan" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white xl:col-span-2">
                <select name="status" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                    <option value="">Semua status</option>
                    @foreach ($orderStatuses as $value => $label)
                        <option value="{{ $value }}" @selected(request('status') === $value)>{{ $label }}</option>
                    @endforeach
                </select>
                <select name="channel" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                    <option value="">Semua kanal</option>
                    <option value="erp" @selected(request('channel') === 'erp')>ERP</option>
                    <option value="web" @selected(request('channel') === 'web')>Web</option>
                    <option value="marketplace" @selected(request('channel') === 'marketplace')>Marketplace</option>
                </select>
                <input name="date_from" type="date" value="{{ request('date_from') }}" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                <input name="date_to" type="date" value="{{ request('date_to') }}" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                <x-ui.button type="submit" variant="secondary">Filter</x-ui.button>
            </form>
        </x-ui.card>

        <x-ui.card padding="p-0" class="overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full min-w-[800px] text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3">SO</th>
                            <th class="px-5 py-3">Pelanggan</th>
                            <th class="px-5 py-3">Item</th>
                            <th class="px-5 py-3">Total</th>
                            <th class="px-5 py-3">Terbayar</th>
                            <th class="px-5 py-3">Status</th>
                            <th class="px-5 py-3 text-right">Aksi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse ($orders as $order)
                            @php $paid = (float) ($order->verified_payments_sum ?? 0); @endphp
                            <tr class="hover:bg-white/[0.02]">
                                <td class="px-5 py-4 font-mono text-cyan-300">{{ $order->order_number }}</td>
                                <td class="px-5 py-4 text-white">{{ $order->customer?->name ?? 'Walk-in' }}</td>
                                <td class="px-5 py-4 text-slate-400">{{ $order->items->count() }} baris</td>
                                <td class="px-5 py-4 text-white">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 {{ $paid >= $order->grand_total ? 'text-emerald-300' : 'text-amber-300' }}">Rp {{ number_format($paid, 0, ',', '.') }}</td>
                                <td class="px-5 py-4"><span class="rounded-full bg-violet-400/10 px-2 py-0.5 text-xs text-violet-200">{{ $orderStatuses[$order->status] ?? $order->status }}</span></td>
                                <td class="px-5 py-4 text-right">
                                    <a href="{{ route('admin.orders.show', $order) }}" class="text-sm font-medium text-cyan-300 hover:text-cyan-200">Detail →</a>
                                </td>
                            </tr>
                        @empty
                            <tr><td colspan="7" class="px-5 py-12 text-center text-slate-400">Belum ada sales order.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
            @if ($orders->hasPages())
                <div class="border-t border-white/10 px-5 py-4">{{ $orders->links() }}</div>
            @endif
        </x-ui.card>

        <x-ui.modal name="customer-form" title="Pelanggan" subtitle="Data pelanggan untuk sales order.">
            <form method="POST" :action="customerFormAction()" class="space-y-4">
                @csrf
                <template x-if="customerMode === 'edit'"><input type="hidden" name="_method" value="PUT"></template>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Nama</label>
                    <input name="name" x-model="customer.name" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Telepon</label>
                        <input name="phone" x-model="customer.phone" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Email</label>
                        <input name="email" type="email" x-model="customer.email" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white">
                    </div>
                </div>
                <div>
                    <label class="mb-1.5 block text-xs uppercase tracking-wider text-slate-400">Alamat</label>
                    <textarea name="address" x-model="customer.address" rows="2" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white"></textarea>
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'customer-form')">Batal</x-ui.button>
                    <x-ui.button type="submit" x-text="customerMode === 'create' ? 'Simpan Pelanggan' : 'Perbarui Pelanggan'"></x-ui.button>
                </div>
            </form>
        </x-ui.modal>

        <x-ui.modal name="order-form" title="Buat Sales Order" subtitle="Multi-item, reservasi stok opsional, kanal penjualan." maxWidth="xl">
            <form method="POST" action="{{ route('admin.orders.store') }}" class="space-y-4">
                @csrf
                @if ($errors->any() && session('open_modal') === 'order-form')
                    <x-ui.alert type="error">{{ $errors->first() }}</x-ui.alert>
                @endif
                <div class="grid gap-4 sm:grid-cols-2">
                    <div>
                        <label class="mb-1.5 block text-xs uppercase text-slate-400">Pelanggan</label>
                        <select name="customer_id" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            <option value="">Walk-in</option>
                            @foreach ($customers as $customer)
                                <option value="{{ $customer->id }}">{{ $customer->name }}</option>
                            @endforeach
                        </select>
                    </div>
                    <div>
                        <label class="mb-1.5 block text-xs uppercase text-slate-400">Kanal</label>
                        <select name="channel" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            <option value="erp">ERP</option>
                            <option value="web">Web</option>
                            <option value="marketplace">Marketplace</option>
                        </select>
                    </div>
                </div>
                <label class="flex items-center gap-2 text-sm text-slate-300">
                    <input type="hidden" name="reserve_stock" value="0">
                    <input type="checkbox" name="reserve_stock" value="1" class="rounded border-white/20">
                    Reservasi stok otomatis saat dibuat
                </label>
                <div class="space-y-3 rounded-xl border border-white/10 bg-white/5 p-4">
                    <div class="flex items-center justify-between">
                        <p class="text-xs font-medium uppercase tracking-wider text-slate-400">Item Pesanan</p>
                        <button type="button" @click="addLine()" class="text-xs text-cyan-300 hover:text-cyan-200">+ Tambah baris</button>
                    </div>
                    <template x-for="(line, index) in lineItems" :key="index">
                        <div class="grid gap-2 rounded-lg border border-white/10 p-3 sm:grid-cols-12">
                            <div class="sm:col-span-6">
                                <select :name="`items[${index}][product_id]`" x-model="line.product_id" required class="erp-field w-full rounded-lg border border-white/10 bg-slate-950 px-2 py-2 text-sm text-white">
                                    <option value="">Produk</option>
                                    @foreach ($products as $product)
                                        <option value="{{ $product->id }}">{{ $product->sku }} — {{ $product->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="sm:col-span-2">
                                <input :name="`items[${index}][qty]`" type="number" min="1" x-model="line.qty" required placeholder="Qty" class="erp-field w-full rounded-lg border border-white/10 bg-slate-950 px-2 py-2 text-sm text-white">
                            </div>
                            <div class="sm:col-span-3">
                                <input :name="`items[${index}][price]`" type="number" min="0" x-model="line.price" placeholder="Harga (opsional)" class="erp-field w-full rounded-lg border border-white/10 bg-slate-950 px-2 py-2 text-sm text-white">
                            </div>
                            <div class="flex items-center sm:col-span-1">
                                <button type="button" @click="removeLine(index)" class="text-xs text-rose-300" x-show="lineItems.length > 1">×</button>
                            </div>
                        </div>
                    </template>
                </div>
                <div class="grid gap-4 sm:grid-cols-2">
                    <input name="shipping_cost" type="number" min="0" value="0" placeholder="Ongkir" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                    <input name="discount_amount" type="number" min="0" value="0" placeholder="Diskon" class="erp-field rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                </div>
                <div class="flex justify-end gap-2 border-t border-white/10 pt-4">
                    <x-ui.button type="button" variant="secondary" @click="$dispatch('close-modal', 'order-form')">Batal</x-ui.button>
                    <x-ui.button type="submit">Buat Sales Order</x-ui.button>
                </div>
            </form>
        </x-ui.modal>
    </div>
</x-layouts.admin>
