@props(['label' => null, 'rows' => 3])

<div class="space-y-1.5">
    @if($label)
        <label class="block text-xs font-medium uppercase tracking-wider text-slate-400">{{ $label }}</label>
    @endif
    <textarea
        rows="{{ $rows }}"
        {{ $attributes->class('erp-field w-full rounded-xl border border-white/10 bg-slate-950/80 px-3.5 py-2.5 text-sm text-white placeholder:text-slate-500 focus:border-cyan-400/50 focus:outline-none focus:ring-2 focus:ring-cyan-400/20') }}
    >{{ $slot }}</textarea>
    @error($attributes->get('name'))
        <p class="text-xs text-rose-300">{{ $message }}</p>
    @enderror
</div>
