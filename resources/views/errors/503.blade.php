<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>503 - Layanan Tidak Tersedia</title>
    @vite(['resources/css/app.css'])
</head>
<body class="min-h-screen bg-slate-950 text-slate-100 antialiased">
    <main class="mx-auto flex min-h-screen max-w-3xl flex-col items-center justify-center px-6 text-center">
        <p class="text-sm uppercase tracking-[0.25em] text-violet-300">Error 503</p>
        <h1 class="mt-3 text-3xl font-semibold text-white">Layanan Sementara Tidak Tersedia</h1>
        <p class="mt-4 text-slate-300">Aplikasi sedang dalam pemeliharaan. Silakan kembali beberapa saat lagi.</p>
        <a href="{{ url('/') }}" class="mt-8 rounded-xl border border-white/15 bg-white/5 px-5 py-2.5 text-sm font-medium text-white transition hover:bg-white/10">
            Kembali ke Beranda
        </a>
    </main>
</body>
</html>
