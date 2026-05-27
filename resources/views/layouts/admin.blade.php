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
    <div class="min-h-screen lg:grid lg:grid-cols-[280px_1fr]">
        <aside class="sticky top-0 hidden h-screen border-r border-white/10 bg-slate-900/60 p-6 backdrop-blur lg:block">
            <div class="mb-8">
                <p class="text-xs uppercase tracking-[0.2em] text-rose-400">GPDISTRO Racing</p>
                <h1 class="mt-2 text-xl font-bold">Enterprise Suite</h1>
            </div>
            <nav class="space-y-2 text-sm">
                <a href="{{ route('admin.dashboard') }}" class="block rounded-xl px-4 py-3 transition hover:bg-white/10">Dashboard</a>
                <a href="#" class="block rounded-xl px-4 py-3 text-slate-400 transition hover:bg-white/10 hover:text-white">Inventory</a>
                <a href="#" class="block rounded-xl px-4 py-3 text-slate-400 transition hover:bg-white/10 hover:text-white">Purchasing</a>
                <a href="#" class="block rounded-xl px-4 py-3 text-slate-400 transition hover:bg-white/10 hover:text-white">Production</a>
                <a href="#" class="block rounded-xl px-4 py-3 text-slate-400 transition hover:bg-white/10 hover:text-white">Finance</a>
                <a href="#" class="block rounded-xl px-4 py-3 text-slate-400 transition hover:bg-white/10 hover:text-white">Ecommerce</a>
            </nav>
        </aside>

        <div class="flex min-h-screen flex-col">
            <header class="sticky top-0 z-20 border-b border-white/10 bg-slate-950/80 px-4 py-4 backdrop-blur sm:px-8">
                <div class="mx-auto flex w-full max-w-7xl items-center justify-between">
                    <button @click="mobileMenu = !mobileMenu" class="rounded-lg border border-white/10 p-2 lg:hidden">☰</button>
                    <div>
                        <p class="text-xs uppercase tracking-[0.2em] text-slate-400">Admin</p>
                        <h2 class="text-lg font-semibold">{{ $header ?? 'Dashboard' }}</h2>
                    </div>
                    <div class="text-right">
                        <p class="text-sm font-medium">{{ auth()->user()->name ?? 'Guest' }}</p>
                        <p class="text-xs text-slate-400">{{ auth()->user()?->getRoleNames()->implode(', ') }}</p>
                    </div>
                </div>
            </header>
            <main class="mx-auto w-full max-w-7xl flex-1 px-4 py-8 sm:px-8">
                {{ $slot }}
            </main>
        </div>
    </div>

    <div x-show="mobileMenu" x-transition class="fixed inset-0 z-30 bg-black/70 p-6 lg:hidden">
        <div class="rounded-2xl border border-white/10 bg-slate-900 p-4">
            <a href="{{ route('admin.dashboard') }}" class="block rounded-lg px-4 py-3 hover:bg-white/10">Dashboard</a>
        </div>
    </div>
</body>
</html>
