<x-layouts.admin :title="'ERP Dashboard'" :header="'ERP Command Center'">
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $card)
            <x-ui.stat-card :label="$card['label']" :value="$card['value']" :trend="$card['trend']" />
        @endforeach
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-3">
        <x-ui.card class="bg-gradient-to-br from-violet-600/20 via-slate-900/80 to-cyan-700/15 xl:col-span-2">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Operational Overview</h3>
                <span class="rounded-full border border-white/20 bg-white/10 px-2.5 py-1 text-[11px] text-slate-200">Monthly</span>
            </div>
            <p class="mt-3 text-sm text-slate-300">Performance snapshot across sales, fulfillment, production throughput, and channel contribution.</p>
            <div class="mt-6 grid gap-4 sm:grid-cols-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Conversion Rate</p>
                    <p class="mt-2 text-xl font-semibold text-white">6.8%</p>
                    <p class="text-xs text-emerald-300">+1.1% from last week</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Avg. Fulfillment</p>
                    <p class="mt-2 text-xl font-semibold text-white">14.2 hrs</p>
                    <p class="text-xs text-emerald-300">On-track with SLA</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-xs uppercase tracking-wider text-slate-400">Production Yield</p>
                    <p class="mt-2 text-xl font-semibold text-white">97.4%</p>
                    <p class="text-xs text-emerald-300">Improving steadily</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Alerts & Notes" class="bg-slate-900/70">
            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                <li class="rounded-xl border border-emerald-400/20 bg-emerald-400/10 p-3 text-emerald-200">No critical alerts this shift.</li>
                <li class="rounded-xl border border-white/10 bg-white/5 p-3">Queue throughput is healthy.</li>
                <li class="rounded-xl border border-white/10 bg-white/5 p-3">2 procurement requests awaiting approval.</li>
            </ul>
        </x-ui.card>
    </section>

    <section class="mt-8 grid gap-6 lg:grid-cols-2">
        <x-ui.card class="bg-slate-900/65">
            <div class="flex items-center justify-between">
                <h3 class="text-lg font-semibold text-white">Recent Activity</h3>
                <a href="#" class="text-xs font-medium text-cyan-300 hover:text-cyan-200">View all</a>
            </div>
            <div class="mt-4 space-y-3">
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">SO-2026-0512 packed and ready to ship</p>
                    <p class="mt-1 text-xs text-slate-400">2 minutes ago · Warehouse A</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">Payment verified for INV-2026-1201</p>
                    <p class="mt-1 text-xs text-slate-400">11 minutes ago · Finance</p>
                </div>
                <div class="rounded-xl border border-white/10 bg-white/5 p-4">
                    <p class="text-sm text-white">Production batch PRD-332 entered QC stage</p>
                    <p class="mt-1 text-xs text-slate-400">28 minutes ago · Production</p>
                </div>
            </div>
        </x-ui.card>

        <x-ui.card title="Quick Actions" class="bg-slate-900/65">
            <div class="mt-4 grid grid-cols-2 gap-3">
                <x-ui.action-tile>Create Sales Order</x-ui.action-tile>
                <x-ui.action-tile>Record Payment</x-ui.action-tile>
                <x-ui.action-tile>Stock Adjustment</x-ui.action-tile>
                <x-ui.action-tile>Create Expense</x-ui.action-tile>
            </div>
        </x-ui.card>
    </section>
</x-layouts.admin>
