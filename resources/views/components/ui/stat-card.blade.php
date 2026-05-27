@props([
    'label',
    'value',
    'trend' => null,
    'badge' => 'Live',
])

<x-ui.card class="group bg-gradient-to-b from-slate-900/80 to-slate-900/30 shadow-xl shadow-black/25 transition hover:border-white/20 hover:shadow-2xl">
    <div class="flex items-start justify-between">
        <p class="text-sm text-slate-400">{{ $label }}</p>
        <span class="rounded-full border border-emerald-300/30 bg-emerald-400/10 px-2 py-1 text-[10px] font-semibold uppercase tracking-wide text-emerald-300">
            {{ $badge }}
        </span>
    </div>

    <p class="mt-3 text-3xl font-bold text-white">{{ $value }}</p>

    @if($trend)
        <p class="mt-2 text-xs font-medium text-emerald-300">{{ $trend }}</p>
    @endif
</x-ui.card>
