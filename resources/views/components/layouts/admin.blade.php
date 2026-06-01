<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}" class="h-full" x-data="{ mobileMenu: false }">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <title>{{ $title ?? config('app.name', 'GPDISTRO ERP') }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="h-full bg-slate-950 text-slate-100 antialiased">
    <div class="fixed inset-0 -z-10">
        <div class="absolute -left-40 -top-28 h-80 w-80 rounded-full bg-violet-500/20 blur-3xl"></div>
        <div class="absolute right-0 top-1/3 h-72 w-72 rounded-full bg-cyan-400/20 blur-3xl"></div>
        <div class="absolute inset-0 bg-[radial-gradient(circle_at_top,_rgba(148,163,184,0.12),_transparent_60%)]"></div>
    </div>

    <div class="min-h-screen lg:grid lg:grid-cols-[270px_1fr]">
        <aside class="hidden border-r border-white/10 bg-slate-900/70 px-5 py-6 backdrop-blur-xl lg:flex lg:min-h-screen lg:flex-col">
            <div class="mb-8 flex items-center gap-3">
                <div class="grid h-10 w-10 place-content-center rounded-xl bg-gradient-to-br from-violet-500 to-cyan-400 font-bold text-slate-950">GP</div>
                <div>
                    <p class="text-[11px] uppercase tracking-[0.24em] text-violet-200">GPDistro</p>
                    <p class="text-sm font-semibold text-white">ERP Premium</p>
                </div>
            </div>

            <nav class="space-y-1.5 text-sm">
                <a href="{{ route('admin.dashboard') }}" @class([
                    'flex items-center gap-3 rounded-xl px-3.5 py-2.5 transition',
                    'bg-white/10 text-white shadow-lg shadow-black/20' => request()->routeIs('admin.dashboard'),
                    'text-slate-300 hover:bg-white/5 hover:text-white' => !request()->routeIs('admin.dashboard'),
                ])>
                    <span class="inline-block h-2 w-2 rounded-full bg-cyan-300"></span>
                    Dasbor
                </a>
                <a href="{{ route('admin.inventory.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-300 transition hover:bg-white/5 hover:text-white">
                    <span class="inline-block h-2 w-2 rounded-full {{ request()->routeIs('admin.inventory.*') ? 'bg-cyan-300' : 'bg-slate-500' }}"></span>
                    Inventori
                </a>
                <a href="{{ route('admin.products.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-300 transition hover:bg-white/5 hover:text-white">
                    <span class="inline-block h-2 w-2 rounded-full {{ request()->routeIs('admin.products.*') ? 'bg-cyan-300' : 'bg-slate-500' }}"></span>
                    Produk
                </a>
                <a href="{{ route('admin.warehouses.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-300 transition hover:bg-white/5 hover:text-white">
                    <span class="inline-block h-2 w-2 rounded-full {{ request()->routeIs('admin.warehouses.*') ? 'bg-cyan-300' : 'bg-slate-500' }}"></span>
                    Gudang
                </a>
                <a href="{{ route('admin.purchasing.index') }}" class="flex items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-300 transition hover:bg-white/5 hover:text-white">
                    <span class="inline-block h-2 w-2 rounded-full {{ request()->routeIs('admin.purchasing.*') ? 'bg-cyan-300' : 'bg-slate-500' }}"></span>
                    Pembelian
                </a>
                <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-500">
                    <span class="inline-block h-2 w-2 rounded-full bg-slate-500"></span>
                    Produksi <small class="ml-auto text-[10px] uppercase">Segera</small>
                </span>
                <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-500">
                    <span class="inline-block h-2 w-2 rounded-full bg-slate-500"></span>
                    Keuangan <small class="ml-auto text-[10px] uppercase">Segera</small>
                </span>
                <span class="flex cursor-not-allowed items-center gap-3 rounded-xl px-3.5 py-2.5 text-slate-500">
                    <span class="inline-block h-2 w-2 rounded-full bg-slate-500"></span>
                    E-niaga <small class="ml-auto text-[10px] uppercase">Segera</small>
                </span>
            </nav>

            <div class="mt-auto rounded-2xl border border-white/10 bg-white/5 p-4">
                <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Status Sistem</p>
                <p class="mt-2 text-sm font-medium text-white">Mode lokal aktif</p>
                <p class="mt-1 text-xs text-amber-300">SQLite dan server Laravel</p>
            </div>
        </aside>

        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/75 px-4 py-4 backdrop-blur-xl sm:px-8">
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between gap-4">
                    <div class="flex items-center gap-3">
                        <button @click="mobileMenu = true" class="grid h-10 w-10 place-content-center rounded-xl border border-white/15 bg-white/5 text-slate-200 lg:hidden">
                            ☰
                        </button>
                        <div>
                            <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Ruang Kerja Admin</p>
                            <h2 class="text-lg font-semibold text-white">{{ $header ?? 'Dasbor' }}</h2>
                        </div>
                    </div>
                    <div class="flex items-center gap-3">
                        <div class="rounded-xl border border-white/15 bg-white/5 px-3 py-2 text-right">
                            <p class="text-sm font-medium text-white">{{ auth()->user()->name ?? 'Tamu' }}</p>
                            <p class="text-xs text-slate-400">{{ auth()->user()?->getRoleNames()->implode(', ') ?: 'Tanpa peran' }}</p>
                        </div>
                    </div>
                </div>
            </header>

            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div x-show="mobileMenu" x-transition.opacity class="fixed inset-0 z-30 bg-slate-950/80 p-4 lg:hidden" @click.self="mobileMenu = false">
        <div class="h-full max-w-xs rounded-2xl border border-white/10 bg-slate-900 p-5 shadow-2xl shadow-black/60">
            <div class="mb-6 flex items-center justify-between">
                <p class="text-sm font-semibold text-white">Navigasi</p>
                <button @click="mobileMenu = false" class="rounded-lg border border-white/10 px-2 py-1 text-xs text-slate-300">Tutup</button>
            </div>
            <a href="{{ route('admin.dashboard') }}" class="block rounded-xl bg-white/10 px-4 py-3 text-sm font-medium text-white">Dasbor</a>
            <a href="{{ route('admin.inventory.index') }}" class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-300">Inventori</a>
            <a href="{{ route('admin.products.index') }}" class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-300">Produk</a>
            <a href="{{ route('admin.warehouses.index') }}" class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-300">Gudang</a>
            <a href="{{ route('admin.purchasing.index') }}" class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-300">Pembelian</a>
            <span class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-500">Produksi - segera</span>
            <span class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-500">Keuangan - segera</span>
            <span class="mt-2 block rounded-xl px-4 py-3 text-sm text-slate-500">E-niaga - segera</span>
        </div>
    </div>
</body>
</html>
