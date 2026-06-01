@props([
    'title',
    'description' => null,
])

<div {{ $attributes->class('mb-6 flex flex-col gap-4 sm:flex-row sm:items-end sm:justify-between') }}>
    <div>
        <h1 class="text-2xl font-bold tracking-tight text-white">{{ $title }}</h1>
        @if($description)
            <p class="mt-1 max-w-2xl text-sm text-slate-400">{{ $description }}</p>
        @endif
    </div>
    @if(trim($slot))
        <div class="flex flex-wrap items-center gap-2">
            {{ $slot }}
        </div>
    @endif
</div>
