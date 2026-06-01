@props([
    'variant' => 'primary',
    'type' => 'button',
    'size' => 'md',
])

@php
$classes = match ($variant) {
    'primary' => 'border-cyan-300/40 bg-cyan-300 text-slate-950 hover:bg-cyan-200',
    'secondary' => 'border-white/15 bg-white/5 text-slate-100 hover:bg-white/10',
    'danger' => 'border-rose-400/30 bg-rose-500/10 text-rose-200 hover:bg-rose-500/20',
    'ghost' => 'border-transparent bg-transparent text-slate-300 hover:bg-white/5 hover:text-white',
    default => 'border-white/15 bg-white/5 text-slate-100 hover:bg-white/10',
};
$sizeClass = $size === 'sm' ? 'px-3 py-1.5 text-xs' : 'px-4 py-2 text-sm';
@endphp

<button
    type="{{ $type }}"
    {{ $attributes->class("inline-flex items-center justify-center gap-2 rounded-xl border font-semibold transition {$sizeClass} {$classes}") }}
>
    {{ $slot }}
</button>
