@php
    $user = auth()->user();
    $is = fn (string ...$patterns) => request()->routeIs(...$patterns);
@endphp
<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="description" content="Sistem Informasi Akuntansi & POS Toko Listrik Berkat Jaya">
    <title>{{ isset($title) ? $title . ' · ' : '' }}Berkat Jaya ERP</title>
    <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">
    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800|jetbrains-mono:400,600,700&display=swap" rel="stylesheet" />
    @vite(['resources/css/app.css', 'resources/js/app.js'])
</head>
<body class="bg-gunmetal-50 font-sans text-slate-800" x-data="{ nav: false }">

<div class="min-h-screen lg:pl-64">
    {{-- ============ SIDEBAR ============ --}}
    <div x-cloak x-show="nav" x-transition.opacity class="fixed inset-0 z-30 bg-black/50 lg:hidden" @click="nav = false"></div>

    <aside class="fixed inset-y-0 left-0 z-40 flex w-64 -translate-x-full flex-col bg-gunmetal text-slate-300 transition-transform duration-200 lg:translate-x-0"
           :class="nav && 'translate-x-0'">
        <a href="{{ $user->homeRoute() }}" class="flex items-center gap-3 border-b border-white/5 px-5 py-4">
            <img src="{{ asset('images/logo.webp') }}" alt="Logo Berkat Jaya" class="h-9 w-9 rounded-md bg-white/5 object-contain p-0.5">
            <div class="leading-tight">
                <div class="text-sm font-extrabold tracking-wide text-white">BERKAT JAYA</div>
                <div class="text-[10px] font-semibold uppercase tracking-[0.2em] text-teal-400">Toko Listrik · ERP</div>
            </div>
        </a>

        <nav class="flex-1 overflow-y-auto px-3 pb-6" aria-label="Navigasi utama">
            @if ($user->hasRole('owner', 'admin'))
                <div class="side-group">Ringkasan</div>
                <x-side-link :href="route('dashboard')" :active="$is('dashboard')" icon="home">Dashboard</x-side-link>
            @endif

            <div class="side-group">Transaksi</div>
            @if ($user->hasRole('owner', 'cashier'))
                <x-side-link :href="route('pos.index')" :active="$is('pos.*')" icon="pos">POS Kasir</x-side-link>
            @endif
            @if ($user->hasRole('owner', 'admin'))
                <x-side-link :href="route('sales.index')" :active="$is('sales.*')" icon="receipt">Riwayat Penjualan</x-side-link>
                <x-side-link :href="route('purchase-orders.index')" :active="$is('purchase-orders.*')" icon="truck">Pembelian (PO)</x-side-link>
            @endif
            @if ($user->isOwner())
                <x-side-link :href="route('cash.index')" :active="$is('cash.*')" icon="wallet">Kas, Modal & Beban</x-side-link>
            @endif

            @if ($user->hasRole('owner', 'admin'))
                <div class="side-group">Master Data</div>
                <x-side-link :href="route('products.index')" :active="$is('products.*')" icon="box">Produk & Stok</x-side-link>
                <x-side-link :href="route('units.index')" :active="$is('units.*')" icon="ruler">Satuan</x-side-link>
                <x-side-link :href="route('suppliers.index')" :active="$is('suppliers.*')" icon="factory">Supplier</x-side-link>
            @endif

            @if ($user->isOwner())
                <div class="side-group">Akuntansi</div>
                <x-side-link :href="route('reports.journals')" :active="$is('reports.journals')" icon="book">Jurnal Umum</x-side-link>
                <x-side-link :href="route('reports.ledger')" :active="$is('reports.ledger')" icon="ledger">Buku Besar</x-side-link>
                <x-side-link :href="route('reports.trial-balance')" :active="$is('reports.trial-balance')" icon="scale">Neraca Saldo</x-side-link>
                <x-side-link :href="route('reports.income-statement')" :active="$is('reports.income-statement')" icon="chart">Laba Rugi</x-side-link>
                <x-side-link :href="route('reports.balance-sheet')" :active="$is('reports.balance-sheet')" icon="bank">Posisi Keuangan</x-side-link>

                <div class="side-group">Pengaturan</div>
                <x-side-link :href="route('users.index')" :active="$is('users.*')" icon="users">Pengguna</x-side-link>
            @endif
        </nav>

        <div class="border-t border-white/5 p-3">
            <div class="flex items-center gap-3 rounded-md px-2 py-2">
                <div class="grid h-9 w-9 place-items-center rounded-md bg-teal-600/20 text-sm font-bold text-teal-300">
                    {{ strtoupper(mb_substr($user->name, 0, 1)) }}
                </div>
                <a href="{{ route('profile.edit') }}" class="min-w-0 flex-1 leading-tight hover:text-white">
                    <div class="truncate text-sm font-semibold text-white">{{ $user->name }}</div>
                    <div class="text-[11px] text-slate-400">{{ $user->roleLabel() }}</div>
                </a>
                <form method="POST" action="{{ route('logout') }}">
                    @csrf
                    <button type="submit" title="Keluar" class="rounded p-1.5 text-slate-400 hover:bg-white/5 hover:text-red-400" id="logout-button">
                        <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="1.8" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M15 3h4a2 2 0 012 2v14a2 2 0 01-2 2h-4M10 17l5-5-5-5M15 12H3"/></svg>
                    </button>
                </form>
            </div>
        </div>
    </aside>

    {{-- ============ MAIN ============ --}}
    <div class="flex min-h-screen flex-col">
        <header class="sticky top-0 z-20 border-b border-slate-200 bg-white/90 backdrop-blur">
            <div class="flex items-center gap-3 px-4 py-3 sm:px-6 lg:px-8">
                <button type="button" class="rounded-md p-2 text-slate-600 hover:bg-slate-100 lg:hidden" @click="nav = true" aria-label="Buka menu">
                    <svg class="h-5 w-5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                </button>
                <div class="min-w-0 flex-1">
                    @isset($header)
                        {{ $header }}
                    @endisset
                </div>
                @isset($actions)
                    <div class="flex shrink-0 flex-wrap items-center gap-2">{{ $actions }}</div>
                @endisset
            </div>
        </header>

        <main class="flex-1 px-4 py-6 sm:px-6 lg:px-8">
            @if (session('success'))
                <div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 6000)" x-transition
                     class="mb-5 flex items-start gap-3 rounded-lg border border-emerald-200 bg-emerald-50 px-4 py-3 text-sm text-emerald-800" role="status">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M5 13l4 4L19 7"/></svg>
                    <span class="flex-1">{{ session('success') }}</span>
                    <button type="button" @click="show = false" class="text-emerald-600 hover:text-emerald-900" aria-label="Tutup">&times;</button>
                </div>
            @endif
            @if (session('error'))
                <div class="mb-5 flex items-start gap-3 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <svg class="mt-0.5 h-4 w-4 shrink-0" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 8v4m0 4h.01"/><circle cx="12" cy="12" r="9"/></svg>
                    <span>{{ session('error') }}</span>
                </div>
            @endif
            @if ($errors->any())
                <div class="mb-5 rounded-lg border border-red-200 bg-red-50 px-4 py-3 text-sm text-red-800" role="alert">
                    <div class="font-semibold">Periksa kembali isian berikut:</div>
                    <ul class="mt-1 list-inside list-disc space-y-0.5">
                        @foreach ($errors->all() as $error)
                            <li>{{ $error }}</li>
                        @endforeach
                    </ul>
                </div>
            @endif

            {{ $slot }}
        </main>

        <footer class="border-t border-slate-200 px-8 py-3 text-xs text-slate-400">
            Berkat Jaya ERP · Sistem Informasi Akuntansi & POS · Pencatatan berbasis SAK EMKM
        </footer>
    </div>
</div>
@stack('scripts')
</body>
</html>
