@props([
    'href' => '#',
])

<a href="{{ $href }}" {{ $attributes->class('rounded-xl border border-white/10 bg-white/5 px-4 py-3 text-sm font-medium text-slate-100 transition hover:bg-white/10') }}>
    {{ $slot }}
</a>
