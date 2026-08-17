@php
    $orderStatuses = [
        'pending' => 'Menunggu',
        'paid' => 'Lunas',
        'processing' => 'Diproses',
        'completed' => 'Selesai',
        'cancelled' => 'Dibatalkan',
    ];
    $paymentMethods = [
        'manual_transfer' => 'Transfer Manual',
        'midtrans' => 'Midtrans',
        'xendit' => 'Xendit',
        'qris' => 'QRIS',
        'virtual_account' => 'Virtual Account',
    ];
@endphp

<x-layouts.admin :title="$order->order_number" :header="'Detail Sales Order'">
    <div class="mb-6">
        <a href="{{ route('admin.orders.index') }}" class="text-sm text-cyan-300 hover:text-cyan-200">← Kembali ke daftar penjualan</a>
    </div>

    @if (session('status'))
        <x-ui.alert type="success" class="mb-5">{{ session('status') }}</x-ui.alert>
    @endif
    @if (session('error'))
        <x-ui.alert type="error" class="mb-5">{{ session('error') }}</x-ui.alert>
    @endif

    <div class="grid gap-6 xl:grid-cols-3">
        <div class="space-y-6 xl:col-span-2">
            <x-ui.card>
                <div class="flex flex-wrap items-start justify-between gap-4">
                    <div>
                        <p class="font-mono text-xs text-cyan-300">{{ $order->order_number }}</p>
                        <h2 class="mt-1 text-2xl font-semibold text-white">{{ $order->customer?->name ?? 'Walk-in' }}</h2>
                        <p class="mt-2 text-sm text-slate-400">
                            Kanal {{ strtoupper($order->channel) }} · {{ $order->created_at->format('d M Y H:i') }}
                        </p>
                    </div>
                    <span class="rounded-full bg-violet-400/10 px-4 py-1.5 text-sm text-violet-200">
                        {{ $orderStatuses[$order->status] ?? $order->status }}
                    </span>
                </div>

                <div class="mt-6 grid gap-3 sm:grid-cols-4">
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Subtotal</p>
                        <p class="mt-1 font-semibold text-white">Rp {{ number_format($order->subtotal, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Ongkir</p>
                        <p class="mt-1 font-semibold text-white">Rp {{ number_format($order->shipping_cost, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-white/10 bg-white/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-slate-400">Diskon</p>
                        <p class="mt-1 font-semibold text-white">Rp {{ number_format($order->discount_amount, 0, ',', '.') }}</p>
                    </div>
                    <div class="rounded-xl border border-cyan-400/20 bg-cyan-400/5 p-3">
                        <p class="text-[10px] uppercase tracking-wider text-cyan-200">Grand Total</p>
                        <p class="mt-1 font-semibold text-white">Rp {{ number_format($order->grand_total, 0, ',', '.') }}</p>
                    </div>
                </div>

                <div class="mt-4 flex flex-wrap gap-4 text-sm">
                    <span class="text-emerald-300">Terbayar: Rp {{ number_format($order->paid_total, 0, ',', '.') }}</span>
                    <span class="{{ $order->balance_due > 0 ? 'text-amber-300' : 'text-slate-400' }}">
                        Sisa: Rp {{ number_format($order->balance_due, 0, ',', '.') }}
                    </span>
                </div>
            </x-ui.card>

            <x-ui.card padding="p-0" class="overflow-hidden">
                <div class="border-b border-white/10 px-5 py-4">
                    <h3 class="font-semibold text-white">Item Pesanan</h3>
                </div>
                <table class="w-full text-left text-sm">
                    <thead class="border-b border-white/10 bg-white/5 text-xs uppercase tracking-wider text-slate-400">
                        <tr>
                            <th class="px-5 py-3">Produk</th>
                            <th class="px-5 py-3">Qty</th>
                            <th class="px-5 py-3">Harga</th>
                            <th class="px-5 py-3">Subtotal</th>
                            <th class="px-5 py-3">Reservasi</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach ($order->items as $item)
                            <tr>
                                <td class="px-5 py-4 text-white">{{ $item->product_name_snapshot }}</td>
                                <td class="px-5 py-4 text-slate-300">{{ $item->qty }}</td>
                                <td class="px-5 py-4 text-slate-300">Rp {{ number_format($item->price, 0, ',', '.') }}</td>
                                <td class="px-5 py-4 text-white">Rp {{ number_format($item->subtotal, 0, ',', '.') }}</td>
                                <td class="px-5 py-4">
                                    @if (!empty($item->meta['reservations']))
                                        <span class="rounded-full bg-amber-400/10 px-2 py-0.5 text-xs text-amber-200">Reserved</span>
                                    @elseif (!empty($item->meta['fulfilled_at']))
                                        <span class="rounded-full bg-emerald-400/10 px-2 py-0.5 text-xs text-emerald-200">Terpenuhi</span>
                                    @else
                                        <span class="text-xs text-slate-500">—</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </x-ui.card>

            <x-ui.card title="Pembayaran">
                <div class="mt-4 space-y-3">
                    @forelse ($order->payments as $payment)
                        <div class="flex flex-wrap items-center justify-between gap-2 rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                            <div>
                                <p class="font-mono text-cyan-300">{{ $payment->payment_number }}</p>
                                <p class="text-slate-400">{{ $paymentMethods[$payment->method] ?? $payment->method }} · {{ $payment->paid_at?->format('d M Y H:i') }}</p>
                            </div>
                            <p class="font-semibold text-emerald-300">Rp {{ number_format($payment->amount, 0, ',', '.') }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada pembayaran tercatat.</p>
                    @endforelse
                </div>

                @can('update', $order)
                    @if (!in_array($order->status, ['cancelled', 'completed'], true) && $order->balance_due > 0)
                        <form method="POST" action="{{ route('admin.orders.payments.store', $order) }}" class="mt-6 grid gap-3 border-t border-white/10 pt-4 sm:grid-cols-3">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs uppercase text-slate-400">Jumlah</label>
                                <input name="amount" type="number" min="0.01" max="{{ $order->balance_due }}" value="{{ $order->balance_due }}" required class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs uppercase text-slate-400">Metode</label>
                                <select name="method" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                                    @foreach ($paymentMethods as $value => $label)
                                        <option value="{{ $value }}">{{ $label }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div class="flex items-end">
                                <x-ui.button type="submit" class="w-full">Catat Pembayaran</x-ui.button>
                            </div>
                        </form>
                    @endif
                @endcan
            </x-ui.card>

            <x-ui.card title="Pengiriman">
                <div class="mt-4 space-y-3">
                    @forelse ($order->shipments as $shipment)
                        <div class="rounded-xl border border-white/10 bg-white/5 p-3 text-sm">
                            <p class="font-mono text-cyan-300">{{ $shipment->shipment_number }}</p>
                            <p class="mt-1 text-white">{{ $shipment->courier }} {{ $shipment->service ? '· '.$shipment->service : '' }}</p>
                            <p class="text-slate-400">Resi: {{ $shipment->tracking_number ?: '—' }} · {{ ucfirst($shipment->status) }}</p>
                        </div>
                    @empty
                        <p class="text-sm text-slate-400">Belum ada data pengiriman.</p>
                    @endforelse
                </div>

                @can('update', $order)
                    @if (in_array($order->status, ['paid', 'processing', 'completed'], true))
                        <form method="POST" action="{{ route('admin.orders.shipments.store', $order) }}" class="mt-6 grid gap-3 border-t border-white/10 pt-4 sm:grid-cols-2">
                            @csrf
                            <div>
                                <label class="mb-1 block text-xs uppercase text-slate-400">Kurir</label>
                                <input name="courier" required placeholder="JNE, J&T, GoSend..." class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            </div>
                            <div>
                                <label class="mb-1 block text-xs uppercase text-slate-400">Layanan</label>
                                <input name="service" placeholder="REG, YES, Instant..." class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            </div>
                            <div class="sm:col-span-2">
                                <label class="mb-1 block text-xs uppercase text-slate-400">No. Resi</label>
                                <input name="tracking_number" class="erp-field w-full rounded-xl border border-white/10 bg-slate-950 px-3 py-2 text-sm text-white">
                            </div>
                            <div class="sm:col-span-2">
                                <x-ui.button type="submit">Simpan Pengiriman</x-ui.button>
                            </div>
                        </form>
                    @endif
                @endcan
            </x-ui.card>
        </div>

        <div class="space-y-6">
            <x-ui.card title="Workflow Pesanan">
                <div class="mt-4 space-y-2">
                    @if ($order->status === 'pending')
                        @can('update', $order)
                            <form method="POST" action="{{ route('admin.orders.reserve', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" variant="secondary" class="w-full">Reservasi Stok</x-ui.button>
                            </form>
                            <form method="POST" action="{{ route('admin.orders.paid', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" variant="secondary" class="w-full">Tandai Lunas Penuh</x-ui.button>
                            </form>
                        @endcan
                    @endif

                    @if (in_array($order->status, ['pending', 'paid'], true))
                        @can('process', $order)
                            <form method="POST" action="{{ route('admin.orders.process', $order) }}" class="space-y-2">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" class="w-full">Proses & Potong Stok</x-ui.button>
                            </form>
                        @endcan
                        @can('update', $order)
                            <form method="POST" action="{{ route('admin.orders.cancel', $order) }}" onsubmit="return confirm('Batalkan pesanan?')">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" variant="secondary" class="w-full">Batalkan</x-ui.button>
                            </form>
                        @endcan
                    @endif

                    @if ($order->status === 'processing')
                        @can('update', $order)
                            <form method="POST" action="{{ route('admin.orders.complete', $order) }}">
                                @csrf
                                @method('PATCH')
                                <x-ui.button type="submit" class="w-full">Selesaikan Pesanan</x-ui.button>
                            </form>
                        @endcan
                    @endif

                    @if ($order->status === 'completed')
                        <p class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-sm text-emerald-200">Pesanan telah selesai dipenuhi.</p>
                    @endif
                    @if ($order->status === 'cancelled')
                        <p class="rounded-xl border border-rose-400/20 bg-rose-400/10 p-3 text-sm text-rose-200">Pesanan dibatalkan.</p>
                    @endif
                </div>
            </x-ui.card>

            @if ($order->customer)
                <x-ui.card title="Pelanggan">
                    <dl class="mt-4 space-y-2 text-sm">
                        <div><dt class="text-slate-500">Nama</dt><dd class="text-white">{{ $order->customer->name }}</dd></div>
                        <div><dt class="text-slate-500">Telepon</dt><dd class="text-slate-300">{{ $order->customer->phone ?: '—' }}</dd></div>
                        <div><dt class="text-slate-500">Email</dt><dd class="text-slate-300">{{ $order->customer->email ?: '—' }}</dd></div>
                    </dl>
                </x-ui.card>
            @endif
        </div>
    </div>
</x-layouts.admin>
