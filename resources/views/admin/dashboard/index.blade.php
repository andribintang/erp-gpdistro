<x-layouts.admin :title="'Dasbor ERP'" :header="'Pusat Komando ERP'">
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $card)
            <x-ui.stat-card :label="$card['label']" :value="$card['value']" :trend="$card['trend']" />
        @endforeach
    </section>

    <section class="mt-8">
        <x-ui.card title="Tren Pendapatan (14 Hari)" class="bg-slate-900/70">
            <div class="mt-6 flex h-40 items-end gap-1.5">
                @foreach ($revenueChart['points'] as $point)
                    @php $height = $point['value'] > 0 ? max(8, ($point['value'] / $revenueChart['max']) * 100) : 4; @endphp
                    <div class="group flex flex-1 flex-col items-center gap-1">
                        <div class="w-full rounded-t-md bg-gradient-to-t from-violet-600 to-cyan-400 opacity-80 transition group-hover:opacity-100" style="height: {{ $height }}%"></div>
                        <span class="hidden text-[9px] text-slate-500 sm:block">{{ $point['label'] }}</span>
                    </div>
                @endforeach
            </div>
            <p class="mt-4 text-xs text-slate-500">Berdasarkan pesanan berstatus lunas, diproses, atau selesai.</p>
        </x-ui.card>
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-3">
        <x-ui.card class="bg-gradient-to-br from-violet-600/20 via-slate-900/80 to-cyan-700/15 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Ringkasan Operasional</h3>
                <span class="rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] text-slate-200">Bulanan</span>
            </div>
            <p class="mt-3 text-sm text-slate-300">Data langsung dari pesanan penjualan, pembelian, dan inventori aktif.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Tingkat Pemenuhan</p>
                    <p class="mt-2 text-xl font-semibold text-white">{{ $summary['fulfillment_rate'] }}%</p>
                    <p class="text-xs text-slate-400">{{ $summary['orders_month'] }} pesanan bulan ini</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">PO Menunggu</p>
                    <p class="mt-2 text-xl font-semibold text-white">{{ $summary['pending_po'] }}</p>
                    <p class="text-xs text-slate-400">
                        @if ($summary['pending_po'] > 0)
                            <a href="{{ route('admin.purchasing.index') }}" class="text-cyan-300 hover:text-cyan-200">Lihat pembelian</a>
                        @else
                            Tidak ada antrian persetujuan
                        @endif
                    </p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">SKU Menipis</p>
                    <p class="mt-2 text-xl font-semibold text-white">{{ $summary['low_stock'] }}</p>
                    <p class="text-xs text-slate-400">
                        @if ($summary['low_stock'] > 0)
                            <a href="{{ route('admin.inventory.index') }}" class="text-cyan-300 hover:text-cyan-200">Lihat inventori</a>
                        @else
                            Stok dalam batas aman
                        @endif
                    </p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Peringatan & Catatan" class="bg-slate-900/70">
            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                @foreach ($alerts as $alert)
                    <li @class([
                        'rounded-xl border p-3',
                        'border-emerald-400/20 bg-emerald-400/10 text-emerald-200' => $alert['type'] === 'success',
                        'border-amber-400/20 bg-amber-400/10 text-amber-100' => $alert['type'] === 'warning',
                        'border-white/10 bg-white/5' => $alert['type'] === 'info',
                    ])>{{ $alert['message'] }}</li>
                @endforeach
            </ul>
        </x-ui.card>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card class="bg-slate-900/65">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Aktivitas Terbaru</h3>
                <a href="{{ route('admin.orders.index') }}" class="text-xs font-medium text-cyan-300 hover:text-cyan-200">Penjualan</a>
            </div>
            <div class="mt-4 space-y-3">
                @forelse ($activities as $activity)
                    <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                        <p class="text-sm text-white">{{ $activity['title'] }}</p>
                        <p class="mt-1 text-xs text-slate-400">{{ $activity['ago'] }} · {{ $activity['meta'] }}</p>
                    </div>
                @empty
                    <p class="rounded-xl border border-white/10 bg-white/5 p-4 text-sm text-slate-400">Belum ada aktivitas tercatat.</p>
                @endforelse
            </div>
        </x-ui.card>

        <x-ui.card title="Aksi Cepat" class="bg-slate-900/65">
            <div class="mt-4 grid grid-cols-2 gap-3">
                <x-ui.action-tile :href="route('admin.orders.index')">Buat Sales Order</x-ui.action-tile>
                <x-ui.action-tile :href="route('admin.purchasing.index')">Purchase Order</x-ui.action-tile>
                <x-ui.action-tile :href="route('admin.inventory.index')">Penyesuaian Stok</x-ui.action-tile>
                <x-ui.action-tile :href="route('admin.products.index')">Kelola Produk</x-ui.action-tile>
            </div>
        </x-ui.card>
    </section>
</x-layouts.admin>
