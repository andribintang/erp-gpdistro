<x-layouts.admin :title="'Dasbor ERP'" :header="'Pusat Komando ERP'">
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $card)
            <x-ui.stat-card :label="$card['label']" :value="$card['value']" :trend="$card['trend']" />
        @endforeach
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-3">
        <x-ui.card class="bg-gradient-to-br from-violet-600/20 via-slate-900/80 to-cyan-700/15 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Ringkasan Operasional</h3>
                <span class="rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] text-slate-200">Bulanan</span>
            </div>
            <p class="mt-3 text-sm text-slate-300">Ringkasan performa penjualan, pemenuhan pesanan, throughput produksi, dan kontribusi kanal.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Tingkat Konversi</p>
                    <p class="mt-2 text-xl font-semibold text-white">6.8%</p>
                    <p class="text-xs text-emerald-300">+1.1% dari minggu lalu</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Rata-rata Pemenuhan</p>
                    <p class="mt-2 text-xl font-semibold text-white">14.2 hrs</p>
                    <p class="text-xs text-emerald-300">Sesuai target SLA</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Yield Produksi</p>
                    <p class="mt-2 text-xl font-semibold text-white">97.4%</p>
                    <p class="text-xs text-emerald-300">Meningkat secara stabil</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Peringatan & Catatan" class="bg-slate-900/70">
            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                <li class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-emerald-200">Tidak ada peringatan kritis pada shift ini.</li>
                <li class="rounded-xl border border-white/10 bg-white/5 p-3">Throughput antrian dalam kondisi baik.</li>
                <li class="rounded-xl border border-white/10 bg-white/5 p-3">2 permintaan pengadaan menunggu persetujuan.</li>
            </ul>
        </x-ui.card>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card class="bg-slate-900/65">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Aktivitas Terbaru</h3>
                <a href="#" class="text-xs font-medium text-cyan-300 hover:text-cyan-200">Lihat semua</a>
            </div>
            <div class="mt-4 space-y-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">SO-2026-0512 sudah dipacking dan siap dikirim</p>
                    <p class="mt-1 text-xs text-slate-400">2 menit lalu · Gudang A</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">Pembayaran untuk INV-2026-1201 telah diverifikasi</p>
                    <p class="mt-1 text-xs text-slate-400">11 menit lalu · Keuangan</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">Batch produksi PRD-332 masuk tahap QC</p>
                    <p class="mt-1 text-xs text-slate-400">28 menit lalu · Produksi</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Aksi Cepat" class="bg-slate-900/65">
            <div class="mt-4 grid grid-cols-2 gap-3">
                <x-ui.action-tile>Buat Sales Order</x-ui.action-tile>
                <x-ui.action-tile>Catat Pembayaran</x-ui.action-tile>
                <x-ui.action-tile>Penyesuaian Stok</x-ui.action-tile>
                <x-ui.action-tile>Buat Pengeluaran</x-ui.action-tile>
            </div>
        </x-ui.card>
    </section>
</x-layouts.admin>
