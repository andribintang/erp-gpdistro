@props([
    'title' => null,
    'subtitle' => null,
    'padding' => 'p-6',
])

<article {{ $attributes->class("rounded-3xl border border-white/10 bg-slate-900/65 {$padding}") }}>
    @if($title || $subtitle)
        <div class="mb-4">
            @if($title)
                <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
            @endif
            @if($subtitle)
                <p class="mt-1 text-sm text-slate-300">{{ $subtitle }}</p>
            @endif
        </div>
    @endif

    {{ $slot }}
</article>
