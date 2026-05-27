<x-layouts.admin :title="'ERP Dashboard'" :header="'ERP Command Center'">
    <section class="grid gap-5 md:grid-cols-2 xl:grid-cols-4">
        @foreach ($stats as $card)
            <article class="rounded-2xl border border-white/10 bg-white/5 p-6 shadow-xl shadow-black/20 backdrop-blur">
                <p class="text-sm text-slate-400">{{ $card['label'] }}</p>
                <p class="mt-2 text-3xl font-bold text-white">{{ $card['value'] }}</p>
                <p class="mt-2 text-xs font-medium text-emerald-400">{{ $card['trend'] }}</p>
            </article>
        @endforeach
    </section>

    <section class="mt-8 grid gap-6 xl:grid-cols-3">
        <article class="rounded-2xl border border-white/10 bg-gradient-to-br from-rose-600/20 via-slate-900 to-slate-800 p-6 xl:col-span-2">
            <h3 class="text-lg font-semibold">Operational Overview</h3>
            <p class="mt-3 text-sm text-slate-300">This panel is prepared for sales trend chart, warehouse movement chart, and marketplace channel contribution.</p>
        </article>
        <article class="rounded-2xl border border-white/10 bg-white/5 p-6">
            <h3 class="text-lg font-semibold">Alerts</h3>
            <ul class="mt-4 space-y-3 text-sm text-slate-300">
                <li class="rounded-lg bg-slate-900/70 p-3">No critical alerts yet.</li>
                <li class="rounded-lg bg-slate-900/70 p-3">Queue and workers are healthy.</li>
            </ul>
        </article>
    </section>
</x-layouts.admin>
