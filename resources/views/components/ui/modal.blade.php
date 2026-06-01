@props([
    'name',
    'title' => null,
    'subtitle' => null,
    'maxWidth' => 'lg',
])

@php
$maxWidthClass = match ($maxWidth) {
    'sm' => 'sm:max-w-sm',
    'md' => 'sm:max-w-md',
    'lg' => 'sm:max-w-lg',
    'xl' => 'sm:max-w-xl',
    '2xl' => 'sm:max-w-2xl',
    default => 'sm:max-w-lg',
};
@endphp

<div
    x-data="{ show: false }"
    x-init="
        $watch('show', value => document.body.classList.toggle('overflow-hidden', value));
        show = @js(session('open_modal') === $name);
    "
    x-on:open-modal.window="if ($event.detail === '{{ $name }}') show = true"
    x-on:close-modal.window="if ($event.detail === '{{ $name }}') show = false"
    x-on:keydown.escape.window="show = false"
    x-show="show"
    x-cloak
    class="fixed inset-0 z-50 overflow-y-auto px-4 py-8"
    style="display: none;"
>
    <div
        x-show="show"
        x-transition.opacity
        class="fixed inset-0 bg-slate-950/80 backdrop-blur-sm"
        @click="show = false"
    ></div>

    <div class="relative flex min-h-full items-center justify-center">
        <div
            x-show="show"
            x-transition
            {{ $attributes->class("w-full {$maxWidthClass} rounded-2xl border border-white/10 bg-slate-900 shadow-2xl shadow-black/50") }}
            @click.stop
        >
            @if($title)
                <div class="border-b border-white/10 px-6 py-4">
                    <div class="flex items-start justify-between gap-4">
                        <div>
                            <h3 class="text-lg font-semibold text-white">{{ $title }}</h3>
                            @if($subtitle)
                                <p class="mt-1 text-sm text-slate-400">{{ $subtitle }}</p>
                            @endif
                        </div>
                        <button type="button" @click="show = false" class="rounded-lg border border-white/10 p-1.5 text-slate-400 hover:bg-white/5 hover:text-white">
                            <span class="sr-only">Tutup</span>
                            ✕
                        </button>
                    </div>
                </div>
            @endif
            <div class="px-6 py-5">
                {{ $slot }}
            </div>
        </div>
    </div>
</div>
