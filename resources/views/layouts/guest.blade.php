<!DOCTYPE html>
<html lang="id">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <meta name="csrf-token" content="{{ csrf_token() }}">
        <meta name="robots" content="noindex, nofollow">

        <title>{{ isset($title) ? $title.' · ' : '' }}Berkat Jaya ERP</title>
        <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">

        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800&display=swap" rel="stylesheet" />

        @vite(['resources/css/app.css', 'resources/js/app.js'])
    </head>
    <body class="relative min-h-screen overflow-x-hidden bg-brand-ink font-sans text-slate-800 antialiased">
        {{-- Dekorasi latar bernuansa teal & oranye logo --}}
        <div aria-hidden="true" class="pointer-events-none absolute inset-0">
            <div class="absolute -left-32 -top-32 h-[28rem] w-[28rem] rounded-full bg-brand-teal/30 blur-3xl"></div>
            <div class="absolute -bottom-40 -right-24 h-[26rem] w-[26rem] rounded-full bg-brand-orange/20 blur-3xl"></div>
            <div class="absolute inset-0 opacity-[0.07]" style="background-image:radial-gradient(#fff 1px,transparent 1px);background-size:22px 22px"></div>
        </div>

        <div class="relative flex min-h-screen flex-col items-center justify-center px-4 py-10">
            <a href="{{ url('/') }}" class="mb-6 block rounded-2xl bg-white p-3 shadow-xl shadow-black/40 ring-1 ring-white/10 transition hover:-translate-y-0.5">
                <img src="{{ asset('images/logo.webp') }}" alt="Berkat Jaya - Toko Listrik" class="h-24 w-auto">
            </a>

            <div class="w-full max-w-md overflow-hidden rounded-2xl bg-white shadow-2xl shadow-black/50">
                <div class="h-1.5 bg-gradient-to-r from-brand-teal via-brand-teal to-brand-orange"></div>
                <div class="px-7 py-7">
                    {{ $slot }}
                </div>
            </div>

            <a href="{{ url('/') }}" class="mt-6 text-sm font-medium text-slate-400 transition hover:text-white">&larr; Kembali ke beranda</a>
            <p class="mt-3 text-xs text-slate-500">&copy; {{ date('Y') }} Berkat Jaya · Toko Listrik</p>
        </div>
    </body>
</html>
