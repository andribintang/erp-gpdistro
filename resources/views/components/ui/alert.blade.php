@props(['type' => 'success'])

@php
$styles = match ($type) {
    'success' => 'border-emerald-400/20 bg-emerald-400/10 text-emerald-200',
    'error' => 'border-rose-400/20 bg-rose-400/10 text-rose-200',
    'warning' => 'border-amber-400/20 bg-amber-400/10 text-amber-200',
    default => 'border-white/10 bg-white/5 text-slate-200',
};
@endphp

<div {{ $attributes->class("rounded-xl border px-4 py-3 text-sm {$styles}") }}>
    {{ $slot }}
</div>
