<!DOCTYPE html>
<html lang="id" class="scroll-smooth">
<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>Toko Listrik Berkat Jaya · Grosir & Eceran Alat Listrik, Kabel & Lampu</title>
    <meta name="description" content="Toko Listrik Berkat Jaya - Pusat penjualan alat listrik terlengkap, kabel SNI, lampu LED, MCB, saklar, dan perlengkapan instalasi untuk rumah tangga, gedung, dan proyek kontraktor.">
    <meta property="og:title" content="Toko Listrik Berkat Jaya · Solusi Kebutuhan Alat Listrik Terlengkap">
    <meta property="og:description" content="Sedia kabel SNI, lampu LED, MCB, saklar, pipa conduit & perkakas listrik. Melayani eceran & partai besar/proyek.">
    <meta property="og:image" content="{{ asset('images/logo.webp') }}">
    <meta name="theme-color" content="#141414">
    <link rel="icon" href="{{ asset('images/logo.webp') }}" type="image/webp">

    <link rel="preconnect" href="https://fonts.bunny.net">
    <link href="https://fonts.bunny.net/css?family=inter:400,500,600,700,800,900&display=swap" rel="stylesheet" />

    @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
        @vite(['resources/css/app.css', 'resources/js/app.js'])
    @endif
</head>
<body class="bg-slate-50 font-sans text-slate-800 antialiased selection:bg-brand-teal selection:text-white" x-data="{ mobileMenu: false }">

    @php
        $storeInfo = $store ?? config('store');
        $whatsappNumber = preg_replace('/[^0-9]/', '', $storeInfo['whatsapp'] ?? '6281234567890');
        $mapsUrl = $storeInfo['maps_url'] ?? 'https://maps.google.com/maps?vet=10CAAQoqAOahcKEwjAu4GyqsGXAxUAAAAAHQAAAAAQCQ..i&udm&fvr=1&pvq=Cg0vZy8xMXM3NjJsNHNzIhYKEHRva28gYmVya2F0IGpheWEQAhgD&lqi=ChB0b2tvIGJlcmthdCBqYXlhWhIiEHRva28gYmVya2F0IGpheWGSARFlbGVjdHJvbmljc19zdG9yZQ&cs=1&um=1&ie=UTF-8&fb=1&gl=id&sa=X&ftid=0x2e708bb91ad3ac63:0x758c36b20c854421';
        $mapsEmbed = $storeInfo['maps_embed'] ?? 'https://maps.google.com/maps?q=toko+berkat+jaya&ftid=0x2e708bb91ad3ac63:0x758c36b20c854421&hl=id&z=16&output=embed';
    @endphp

    {{-- Top Announcement Bar --}}
    <div class="bg-brand-ink text-xs text-slate-300 py-2 border-b border-white/10">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 flex flex-wrap items-center justify-between gap-3">
            <div class="flex items-center gap-4">
                <span class="inline-flex items-center gap-1.5 text-emerald-400 font-medium">
                    <span class="w-2 h-2 rounded-full bg-emerald-400 animate-pulse"></span>
                    Toko Buka: {{ $storeInfo['hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}
                </span>
                <span class="hidden sm:inline-block text-slate-500">|</span>
                <span class="hidden sm:inline-flex items-center gap-1 text-slate-300">
                    <svg class="w-3.5 h-3.5 text-brand-teal" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Siap Kirim Eceran, Grosir & Kebutuhan Proyek
                </span>
            </div>
            <div class="flex items-center gap-4 text-xs font-semibold">
                <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Listrik%20Berkat%20Jaya,%20saya%20mau%20tanya%20harga%20dan%20stok%20alat%20listrik" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-1 text-emerald-400 hover:text-emerald-300 transition">
                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                    WA: {{ $storeInfo['phone'] ?? '0812-3456-7890' }}
                </a>
                @auth
                    <a href="{{ auth()->user()->homeRoute() }}" class="text-brand-orange hover:underline">Dashboard Staff &rarr;</a>
                @else
                    <a href="{{ route('login') }}" class="text-slate-400 hover:text-white transition">Masuk Karyawan</a>
                @endauth
            </div>
        </div>
    </div>

    {{-- Main Navbar --}}
    <header class="sticky top-0 z-40 bg-white/95 backdrop-blur-md border-b border-slate-200/80 shadow-sm">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-20">
                {{-- Logo Toko --}}
                <a href="#beranda" class="flex items-center gap-3 group">
                    <img src="{{ asset('images/logo.webp') }}" alt="Logo Toko Berkat Jaya" class="h-12 w-auto object-contain transition-transform group-hover:scale-105">
                    <div class="hidden sm:block">
                        <span class="block text-xl font-extrabold tracking-tight text-slate-900 group-hover:text-brand-teal transition">BERKAT JAYA</span>
                        <span class="block text-xs font-semibold text-brand-orange uppercase tracking-wider">Toko Listrik & Instalasi</span>
                    </div>
                </a>

                {{-- Desktop Nav Links --}}
                <nav class="hidden md:flex items-center gap-8 text-sm font-semibold text-slate-600">
                    <a href="#beranda" class="hover:text-brand-teal transition">Beranda</a>
                    <a href="#kategori" class="hover:text-brand-teal transition">Kategori Produk</a>
                    <a href="#produk" class="hover:text-brand-teal transition">Produk Unggulan</a>
                    <a href="#keunggulan" class="hover:text-brand-teal transition">Keunggulan</a>
                    <a href="#lokasi" class="inline-flex items-center gap-1.5 text-brand-teal hover:text-emerald-700 transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                        Lokasi & Maps
                    </a>
                </nav>

                {{-- Action CTA --}}
                <div class="flex items-center gap-3">
                    <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Listrik%20Berkat%20Jaya,%20saya%20ingin%20tanya%20stok%20dan%20harga%20alat%20listrik" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-sm px-4 py-2.5 shadow-md shadow-emerald-600/20 transition-all hover:-translate-y-0.5">
                        <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                        <span>Chat WhatsApp</span>
                    </a>

                    <button type="button" @click="mobileMenu = !mobileMenu" class="md:hidden p-2 rounded-lg text-slate-600 hover:bg-slate-100 focus:outline-none" aria-label="Toggle menu">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M4 6h16M4 12h16M4 18h16"/></svg>
                    </button>
                </div>
            </div>
        </div>

        {{-- Mobile dropdown menu --}}
        <div x-cloak x-show="mobileMenu" x-transition class="md:hidden border-t border-slate-200 bg-white px-4 py-4 space-y-3">
            <a href="#beranda" @click="mobileMenu = false" class="block text-sm font-semibold text-slate-700 py-1.5">Beranda</a>
            <a href="#kategori" @click="mobileMenu = false" class="block text-sm font-semibold text-slate-700 py-1.5">Kategori Produk</a>
            <a href="#produk" @click="mobileMenu = false" class="block text-sm font-semibold text-slate-700 py-1.5">Produk Unggulan</a>
            <a href="#keunggulan" @click="mobileMenu = false" class="block text-sm font-semibold text-slate-700 py-1.5">Keunggulan Toko</a>
            <a href="#lokasi" @click="mobileMenu = false" class="block text-sm font-semibold text-brand-teal py-1.5">Lokasi Toko & Maps</a>
            <div class="pt-2 border-t border-slate-100 flex flex-col gap-2">
                <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" class="w-full text-center py-2.5 rounded-lg bg-emerald-600 text-white font-bold text-sm">Hubungi via WhatsApp</a>
                <a href="{{ route('login') }}" class="w-full text-center py-2 rounded-lg bg-slate-100 text-slate-700 font-semibold text-xs">Portal Karyawan (Login)</a>
            </div>
        </div>
    </header>

    {{-- ================= HERO SECTION ================= --}}
    <section id="beranda" class="relative overflow-hidden bg-gradient-to-b from-brand-ink via-[#182625] to-brand-ink text-white py-16 lg:py-24">
        {{-- Background decorative grid & glow --}}
        <div class="absolute inset-0 opacity-10 bg-[radial-gradient(#088F82_1px,transparent_1px)] [background-size:20px_20px] pointer-events-none"></div>
        <div class="absolute -top-40 -right-40 w-96 h-96 bg-brand-teal/20 rounded-full blur-3xl pointer-events-none"></div>
        <div class="absolute -bottom-40 -left-40 w-96 h-96 bg-brand-orange/15 rounded-full blur-3xl pointer-events-none"></div>

        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 lg:grid-cols-12 gap-12 items-center">
                
                {{-- Left Text Info --}}
                <div class="lg:col-span-7 space-y-6 text-center lg:text-left">
                    <div class="inline-flex items-center gap-2 px-3.5 py-1.5 rounded-full bg-white/10 backdrop-blur-md border border-white/15 text-xs font-semibold text-emerald-300">
                        <span class="w-2 h-2 rounded-full bg-brand-orange"></span>
                        Pusat Alat Listrik, Kabel & Lampu Terlengkap
                    </div>

                    <h1 class="text-3xl sm:text-5xl lg:text-6xl font-black tracking-tight leading-tight sm:leading-none text-white">
                        Sedia Lengkap Kebutuhan Listrik <span class="text-transparent bg-clip-text bg-gradient-to-r from-emerald-400 via-teal-300 to-amber-300">Rumah & Proyek</span>
                    </h1>

                    <p class="text-base sm:text-lg text-slate-300 leading-relaxed max-w-2xl mx-auto lg:mx-0">
                        Selamat datang di <strong class="text-white">Toko Listrik Berkat Jaya</strong>. Melayani penjualan kabel bersertifikat SNI, lampu LED hemat energi, MCB & box panel, saklar, pipa instalasi, hingga alat teknik kelistrikan dengan harga eceran dan grosir terbaik.
                    </p>

                    {{-- CTA buttons --}}
                    <div class="pt-2 flex flex-col sm:flex-row items-center justify-center lg:justify-start gap-4">
                        <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Listrik%20Berkat%20Jaya,%20saya%20ingin%20memesan%20alat%20listrik" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-3 px-7 py-4 rounded-xl bg-gradient-to-r from-emerald-500 to-emerald-600 hover:from-emerald-600 hover:to-emerald-700 text-white font-bold text-base shadow-xl shadow-emerald-500/25 transition-all hover:scale-105">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Pesan via WhatsApp</span>
                        </a>

                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="w-full sm:w-auto inline-flex items-center justify-center gap-2 px-6 py-4 rounded-xl bg-white/10 hover:bg-white/20 border border-white/20 text-white font-semibold text-base transition">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Petunjuk Arah (Maps)</span>
                        </a>
                    </div>

                    {{-- Badges / Highlights --}}
                    <div class="pt-6 border-t border-white/10 grid grid-cols-2 sm:grid-cols-4 gap-4 text-left">
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-emerald-500/20 text-emerald-400 flex items-center justify-center font-bold text-sm">✓</div>
                            <span class="text-xs text-slate-300 font-medium">100% Standar SNI</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-amber-500/20 text-amber-400 flex items-center justify-center font-bold text-sm">⚡</div>
                            <span class="text-xs text-slate-300 font-medium">Harga Grosir & Ecer</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-teal-500/20 text-teal-400 flex items-center justify-center font-bold text-sm">📦</div>
                            <span class="text-xs text-slate-300 font-medium">Stok Selalu Siap</span>
                        </div>
                        <div class="flex items-center gap-2">
                            <div class="w-8 h-8 rounded-lg bg-orange-500/20 text-orange-400 flex items-center justify-center font-bold text-sm">🚚</div>
                            <span class="text-xs text-slate-300 font-medium">Siap Kirim Proyek</span>
                        </div>
                    </div>
                </div>

                {{-- Right Hero Showcase Image --}}
                <div class="lg:col-span-5">
                    <div class="relative mx-auto max-w-md lg:max-w-none">
                        <div class="relative rounded-2xl overflow-hidden shadow-2xl border-4 border-white/10 group">
                            <img src="{{ asset('images/hero_store.jpg') }}" alt="Toko Listrik Berkat Jaya" class="w-full h-80 sm:h-96 object-cover object-center group-hover:scale-105 transition duration-700">
                            <div class="absolute inset-0 bg-gradient-to-t from-black/80 via-black/20 to-transparent"></div>
                            
                            {{-- Store Badge Overlay --}}
                            <div class="absolute bottom-4 left-4 right-4 bg-brand-ink/90 backdrop-blur-md p-4 rounded-xl border border-white/15">
                                <div class="flex items-center justify-between">
                                    <div>
                                        <h3 class="text-sm font-bold text-white">Toko Berkat Jaya</h3>
                                        <p class="text-xs text-slate-300">Belanja langsung atau kirim via ekspedisi / kurir</p>
                                    </div>
                                    <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-md bg-emerald-500/20 text-emerald-300 text-xs font-bold">
                                        ● Buka Hari Ini
                                    </span>
                                </div>
                            </div>
                        </div>

                        {{-- Floating pill --}}
                        <div class="absolute -top-4 -left-4 bg-brand-orange text-white text-xs font-black uppercase px-3 py-1.5 rounded-lg shadow-lg tracking-wide">
                            Mitra Listrik Anda
                        </div>
                    </div>
                </div>

            </div>
        </div>
    </section>

    {{-- ================= KATEGORI PRODUK ================= --}}
    <section id="kategori" class="py-16 bg-white border-b border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="text-xs font-bold text-brand-teal uppercase tracking-widest">Katalog Belanja</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Kategori Produk Kelistrikan</h2>
                <p class="text-sm text-slate-600 mt-2">Semua kebutuhan instalasi listrik lengkap tersedia di toko kami dari berbagai merk terkemuka.</p>
            </div>

            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
                {{-- Kategori 1 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-emerald-50/50 border border-slate-200/80 hover:border-emerald-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-emerald-100 text-emerald-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        🔌
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Kabel Listrik</h3>
                    <p class="text-xs text-slate-500 mt-1">NYM, NYA, NYY, Twisted Eterna & Supreme</p>
                </div>

                {{-- Kategori 2 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-amber-50/50 border border-slate-200/80 hover:border-amber-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-amber-100 text-amber-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        💡
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Lampu & LED</h3>
                    <p class="text-xs text-slate-500 mt-1">Bulb, Downlight, Sorot Philips & Hannochs</p>
                </div>

                {{-- Kategori 3 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-teal-50/50 border border-slate-200/80 hover:border-teal-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-teal-100 text-teal-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        🔘
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Saklar & Stop Kontak</h3>
                    <p class="text-xs text-slate-500 mt-1">Broco, Panasonic, Schneider Electric</p>
                </div>

                {{-- Kategori 4 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-red-50/50 border border-slate-200/80 hover:border-red-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-red-100 text-red-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        ⚡
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">MCB & Box Panel</h3>
                    <p class="text-xs text-slate-500 mt-1">Pengaman Arus, ELCB, Fuse & Panel Box</p>
                </div>

                {{-- Kategori 5 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-blue-50/50 border border-slate-200/80 hover:border-blue-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-blue-100 text-blue-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        🪛
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Pipa & Conduit</h3>
                    <p class="text-xs text-slate-500 mt-1">Pipa PVC, Klem, T-Dus, Inbow Doos</p>
                </div>

                {{-- Kategori 6 --}}
                <div class="group p-5 rounded-2xl bg-slate-50 hover:bg-purple-50/50 border border-slate-200/80 hover:border-purple-300 transition-all text-center flex flex-col items-center">
                    <div class="w-14 h-14 rounded-2xl bg-purple-100 text-purple-700 flex items-center justify-center text-2xl mb-3 group-hover:scale-110 transition">
                        🛠️
                    </div>
                    <h3 class="font-bold text-slate-900 text-sm">Alat Teknik</h3>
                    <p class="text-xs text-slate-500 mt-1">Tang, Tespen, Multitester & Isolasi Listrik</p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= PRODUK UNGGULAN / ETALASE ================= --}}
    <section id="produk" class="py-16 bg-slate-50">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="flex flex-col md:flex-row md:items-end justify-between mb-10 gap-4">
                <div>
                    <span class="text-xs font-bold text-brand-orange uppercase tracking-widest">Etalase Toko</span>
                    <h2 class="text-3xl font-extrabold text-slate-900 mt-1">Produk Unggulan & Terlaris</h2>
                    <p class="text-sm text-slate-600 mt-1">Barang ready di toko kami. Pesan langsung via WhatsApp atau datang ke lokasi toko.</p>
                </div>
                <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Berkat%20Jaya,%20saya%20minta%20katalog%20dan%20daftar%20harga%20lengkap" target="_blank" rel="noopener noreferrer" class="inline-flex items-center gap-2 text-sm font-bold text-emerald-600 hover:text-emerald-700">
                    <span>Minta Daftar Harga Lengkap via WA</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M14 5l7 7m0 0l-7 7m7-7H3"/></svg>
                </a>
            </div>

            <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-4 gap-6">
                @if(isset($featured) && count($featured) > 0)
                    @foreach($featured as $p)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-[11px]">{{ $p->sku }}</span>
                                    @if($p->in_stock)
                                        <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">● Ready Stok</span>
                                    @else
                                        <span class="text-amber-600 font-semibold">Pre-order</span>
                                    @endif
                                </div>
                                <h3 class="font-bold text-slate-900 text-base line-clamp-2">{{ $p->name }}</h3>
                                <div class="mt-4">
                                    <div class="text-xs text-slate-500">Harga Satuan ({{ $p->unit }}):</div>
                                    <div class="text-xl font-extrabold text-brand-teal">
                                        Rp {{ number_format($p->price, 0, ',', '.') }}
                                    </div>
                                    @if(!empty($p->others))
                                        <div class="mt-1 text-[11px] text-slate-500">
                                            Satuan lain: {{ implode(', ', $p->others) }}
                                        </div>
                                    @endif
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100">
                                <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Berkat%20Jaya,%20saya%20mau%20beli%20{{ urlencode($p->name) }}%20(SKU:%20{{ $p->sku }})" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>Beli via WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @else
                    {{-- Default Popular Items if database items not seeded yet --}}
                    @php
                        $sampleProducts = [
                            ['name' => 'Kabel Eterna NYM 2 x 1.5 mm (50M)', 'sku' => 'KBL-ETR-NYM215', 'price' => 385000, 'unit' => 'Roll'],
                            ['name' => 'Kabel Supreme NYM 3 x 2.5 mm (50M)', 'sku' => 'KBL-SUP-NYM325', 'price' => 745000, 'unit' => 'Roll'],
                            ['name' => 'Lampu LED Philips MyCare 10W Putih', 'sku' => 'LMP-PHL-10W', 'price' => 45000, 'unit' => 'Pcs'],
                            ['name' => 'Stop Kontak Arde Broco Gracio 15310', 'sku' => 'ACC-BRC-15310', 'price' => 22500, 'unit' => 'Pcs'],
                            ['name' => 'MCB Schneider Domae 1P 10A (2200W)', 'sku' => 'MCB-SCH-1P10A', 'price' => 58000, 'unit' => 'Pcs'],
                            ['name' => 'Saklar Dobel Broco Galleo Putih', 'sku' => 'SKL-BRC-DBL', 'price' => 28000, 'unit' => 'Pcs'],
                            ['name' => 'Pipa Conduit Listrik 20mm Putih (3M)', 'sku' => 'PIP-CND-20MM', 'price' => 14000, 'unit' => 'Batang'],
                            ['name' => 'Isolasi Listrik Nitto 2107 Hitam (20M)', 'sku' => 'ISO-NIT-BLK', 'price' => 9500, 'unit' => 'Roll'],
                        ];
                    @endphp
                    @foreach($sampleProducts as $p)
                        <div class="bg-white rounded-2xl border border-slate-200 shadow-sm hover:shadow-md transition p-5 flex flex-col justify-between">
                            <div>
                                <div class="flex items-center justify-between text-xs text-slate-500 mb-2">
                                    <span class="font-mono bg-slate-100 px-2 py-0.5 rounded text-[11px]">{{ $p['sku'] }}</span>
                                    <span class="inline-flex items-center gap-1 text-emerald-600 font-semibold">● Ready Stok</span>
                                </div>
                                <h3 class="font-bold text-slate-900 text-base line-clamp-2">{{ $p['name'] }}</h3>
                                <div class="mt-4">
                                    <div class="text-xs text-slate-500">Estimasi Harga ({{ $p['unit'] }}):</div>
                                    <div class="text-xl font-extrabold text-brand-teal">
                                        Rp {{ number_format($p['price'], 0, ',', '.') }}
                                    </div>
                                </div>
                            </div>
                            <div class="mt-6 pt-4 border-t border-slate-100">
                                <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Berkat%20Jaya,%20saya%20mau%20beli%20{{ urlencode($p['name']) }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 py-2.5 px-4 rounded-xl bg-emerald-600 hover:bg-emerald-700 text-white font-bold text-xs transition">
                                    <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                                    <span>Beli via WhatsApp</span>
                                </a>
                            </div>
                        </div>
                    @endforeach
                @endif
            </div>
        </div>
    </section>

    {{-- ================= KEUNGGULAN TOKO ================= --}}
    <section id="keunggulan" class="py-16 bg-white border-y border-slate-200">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-14">
                <span class="text-xs font-bold text-brand-teal uppercase tracking-widest">Kenapa Memilih Kami</span>
                <h2 class="text-3xl font-extrabold text-slate-900 mt-2">Belanja Nyaman, Aman & Bergaransi</h2>
                <p class="text-sm text-slate-600 mt-2">Komitmen Toko Listrik Berkat Jaya dalam memberikan pelayanan terbaik untuk setiap pelanggan.</p>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-3 gap-8">
                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-emerald-300 transition">
                    <div class="w-12 h-12 rounded-xl bg-emerald-100 text-emerald-700 flex items-center justify-center font-bold text-xl mb-6">
                        ✓
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Produk Original & Bersertifikat SNI</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Kami menjamin semua produk kabel, MCB, saklar dan komponen kelistrikan adalah barang asli dan standar keselamatan tinggi.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-brand-teal transition">
                    <div class="w-12 h-12 rounded-xl bg-teal-100 text-teal-700 flex items-center justify-center font-bold text-xl mb-6">
                        🏷️
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Harga Kompetitif & Diskon Grosir</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Cocok untuk belanja eceran renovasi rumah maupun kebutuhan kontraktor proyek besar dengan harga partai yang sangat bersahabat.
                    </p>
                </div>

                <div class="p-8 rounded-2xl bg-slate-50 border border-slate-200/80 hover:border-brand-orange transition">
                    <div class="w-12 h-12 rounded-xl bg-orange-100 text-brand-orange flex items-center justify-center font-bold text-xl mb-6">
                        🤝
                    </div>
                    <h3 class="text-lg font-bold text-slate-900 mb-2">Konsultasi Kebutuhan & Respon Cepat</h3>
                    <p class="text-sm text-slate-600 leading-relaxed">
                        Bingung menentukan ukuran kabel atau tipe MCB yang sesuai? Tim kami siap membantu memberikan rekomendasi spesifikasi yang tepat.
                    </p>
                </div>
            </div>
        </div>
    </section>

    {{-- ================= LOKASI & GOOGLE MAPS ================= --}}
    <section id="lokasi" class="py-16 lg:py-24 bg-slate-100/80">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="text-center max-w-2xl mx-auto mb-12">
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-xs font-bold uppercase tracking-wider mb-2">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Lokasi Toko Fisik Kami
                </span>
                <h2 class="text-3xl sm:text-4xl font-extrabold text-slate-900 tracking-tight">Kunjungi Toko Berkat Jaya</h2>
                <p class="text-base text-slate-600 mt-2">Datang langsung untuk memilih barang, cek kualitas material, atau konsultasi langsung dengan staf kami.</p>
            </div>

            <div class="bg-white rounded-3xl border border-slate-200 shadow-xl overflow-hidden grid grid-cols-1 lg:grid-cols-12">
                
                {{-- Detail Alamat & Kontak --}}
                <div class="lg:col-span-5 p-8 sm:p-10 flex flex-col justify-between space-y-8 bg-gradient-to-br from-white to-slate-50">
                    <div class="space-y-6">
                        <div class="flex items-center gap-3">
                            <span class="p-3 rounded-2xl bg-emerald-50 text-emerald-700">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M19 21V5a2 2 0 00-2-2H7a2 2 0 00-2 2v16m14 0h2m-2 0h-5m-9 0H3m2 0h5M9 7h1m-1 4h1m4-4h1m-1 4h1m-5 10v-5a1 1 0 011-1h2a1 1 0 011 1v5m-4 0h4"/></svg>
                            </span>
                            <div>
                                <h3 class="text-xl font-bold text-slate-900">Toko Listrik Berkat Jaya</h3>
                                <p class="text-xs text-slate-500">Retail & Distributor Material Kelistrikan</p>
                            </div>
                        </div>

                        <div class="space-y-4 text-sm">
                            <div class="flex items-start gap-3">
                                <span class="p-2 rounded-lg bg-slate-100 text-slate-600 shrink-0">📍</span>
                                <div>
                                    <div class="font-bold text-slate-900">Alamat Toko:</div>
                                    <div class="text-slate-600 leading-relaxed mt-0.5">
                                        {{ $storeInfo['address'] ?? 'Toko Berkat Jaya - Toko Alat Listrik, Elektronik & Komponen Instalasi' }}
                                    </div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="p-2 rounded-lg bg-slate-100 text-slate-600 shrink-0">🕒</span>
                                <div>
                                    <div class="font-bold text-slate-900">Jam Operasional:</div>
                                    <div class="text-slate-600 mt-0.5">{{ $storeInfo['hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}</div>
                                    <div class="text-xs text-emerald-600 font-semibold mt-0.5">Minggu / Libur Nasional: Hubungi via WhatsApp</div>
                                </div>
                            </div>

                            <div class="flex items-start gap-3">
                                <span class="p-2 rounded-lg bg-slate-100 text-slate-600 shrink-0">📞</span>
                                <div>
                                    <div class="font-bold text-slate-900">Telepon & WhatsApp:</div>
                                    <div class="text-slate-600 mt-0.5">{{ $storeInfo['phone'] ?? '0812-3456-7890' }}</div>
                                </div>
                            </div>
                        </div>
                    </div>

                    {{-- Actions Button Google Maps & WA --}}
                    <div class="space-y-3 pt-4 border-t border-slate-100">
                        {{-- Direct Link to user provided Google Maps URL --}}
                        <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2.5 py-3.5 px-6 rounded-xl bg-gradient-to-r from-brand-teal to-emerald-700 hover:from-teal-700 hover:to-emerald-800 text-white font-bold text-sm shadow-md transition-all hover:shadow-lg">
                            <svg class="w-5 h-5 text-amber-300" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/><path stroke-linecap="round" stroke-linejoin="round" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                            <span>Buka Navigasi di Google Maps</span>
                            <svg class="w-4 h-4 ml-1" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                        </a>

                        <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Berkat%20Jaya,%20saya%20sedang%20menuju%20ke%20toko%20Anda" target="_blank" rel="noopener noreferrer" class="w-full inline-flex items-center justify-center gap-2 py-3 px-6 rounded-xl bg-slate-100 hover:bg-slate-200 text-slate-800 font-semibold text-sm transition">
                            <svg class="w-4 h-4 text-emerald-600" fill="currentColor" viewBox="0 0 24 24"><path d="M.057 24l1.687-6.163c-1.041-1.804-1.588-3.849-1.587-5.946.003-6.556 5.338-11.891 11.893-11.891 3.181.001 6.167 1.24 8.413 3.488 2.245 2.248 3.481 5.236 3.48 8.414-.003 6.557-5.338 11.892-11.893 11.892-1.99-.001-3.951-.5-5.688-1.448l-6.305 1.654zm6.597-3.807c1.676.995 3.276 1.591 5.392 1.592 5.448 0 9.886-4.434 9.889-9.885.002-5.462-4.415-9.89-9.881-9.892-5.452 0-9.887 4.434-9.889 9.884-.001 2.225.651 3.891 1.746 5.634l-.999 3.648 3.742-.981zm11.387-5.464c-.074-.124-.272-.198-.57-.347-.297-.149-1.758-.868-2.031-.967-.272-.099-.47-.149-.669.149-.198.297-.768.967-.941 1.165-.173.198-.347.223-.644.074-.297-.149-1.255-.462-2.39-1.475-.883-.788-1.48-1.761-1.653-2.059-.173-.297-.018-.458.13-.606.134-.133.297-.347.446-.521.151-.172.2-.296.3-.495.099-.198.05-.372-.025-.521-.075-.148-.669-1.611-.916-2.206-.242-.579-.487-.501-.669-.51l-.57-.01c-.198 0-.52.074-.792.372s-1.04 1.016-1.04 2.479 1.065 2.876 1.213 3.074c.149.198 2.095 3.2 5.076 4.487.709.306 1.263.489 1.694.626.712.226 1.36.194 1.872.118.571-.085 1.758-.719 2.006-1.413.248-.695.248-1.29.173-1.414z"/></svg>
                            <span>Tanya Petunjuk via WhatsApp</span>
                        </a>
                    </div>
                </div>

                {{-- Interactive Google Maps Embed --}}
                <div class="lg:col-span-7 relative min-h-[360px] lg:min-h-full bg-slate-200">
                    <iframe 
                        src="{{ $mapsEmbed }}"
                        title="Peta Lokasi Toko Listrik Berkat Jaya"
                        class="w-full h-full min-h-[380px] lg:min-h-[500px] border-0" 
                        allowfullscreen="" 
                        loading="lazy" 
                        referrerpolicy="no-referrer-when-downgrade">
                    </iframe>
                </div>

            </div>
        </div>
    </section>

    {{-- ================= CTA PARTAI BESAR / PROYEK ================= --}}
    <section class="py-14 bg-gradient-to-r from-brand-ink to-[#1a2f2d] text-white">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 text-center sm:text-left flex flex-col sm:flex-row items-center justify-between gap-6">
            <div class="space-y-2">
                <span class="inline-block px-3 py-1 rounded-md bg-brand-orange/20 text-brand-orange font-bold text-xs uppercase tracking-wider">
                    Kebutuhan Kontraktor & Proyek
                </span>
                <h3 class="text-2xl sm:text-3xl font-black">Butuh Penawaran Harga atau Bill of Quantity (BoQ)?</h3>
                <p class="text-sm text-slate-300 max-w-xl">
                    Kirimkan daftar rincian material listrik yang Anda butuhkan. Tim kami siap menghitungkan estimasi harga terbaik dan ketersediaan stok hari ini.
                </p>
            </div>
            <a href="https://wa.me/{{ $whatsappNumber }}?text=Halo%20Toko%20Berkat%20Jaya,%20saya%20punya%20daftar%20kebutuhan%20proyek%20ingin%20minta%20penawaran%20harga" target="_blank" rel="noopener noreferrer" class="shrink-0 px-8 py-4 rounded-xl bg-brand-orange hover:bg-orange-600 text-white font-black text-sm uppercase tracking-wide shadow-xl transition hover:scale-105">
                Kirim BoQ / Minta Penawaran
            </a>
        </div>
    </section>

    {{-- ================= FOOTER ================= --}}
    <footer class="bg-brand-ink text-slate-400 text-sm border-t border-white/10 pt-16 pb-12">
        <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-5 gap-10 mb-12">
                
                {{-- Col 1 & 2: Identitas --}}
                <div class="lg:col-span-2 space-y-4">
                    <div class="flex items-center gap-3">
                        <img src="{{ asset('images/logo.webp') }}" alt="Logo Toko Berkat Jaya" class="h-10 w-auto">
                        <span class="text-xl font-black text-white tracking-wider">BERKAT JAYA</span>
                    </div>
                    <p class="text-xs text-slate-400 leading-relaxed max-w-sm">
                        Pusat penjualan alat-alat listrik, instalasi penerangan, kabel bersertifikat SNI, dan perkakas kelistrikan terlengkap untuk kebutuhan rumah tangga maupun proyek konstruksi.
                    </p>
                    <div class="text-xs text-slate-400 space-y-1">
                        <div>🕒 <strong>Jam Buka:</strong> {{ $storeInfo['hours'] ?? 'Senin - Sabtu: 08.00 - 17.00 WIB' }}</div>
                        <div>📍 <strong>Alamat:</strong> Sedia Lengkap Alat Listrik di Toko Berkat Jaya</div>
                    </div>
                </div>

                {{-- Col 3: Kategori --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Kategori Produk</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#kategori" class="hover:text-white transition">Kabel Listrik SNI</a></li>
                        <li><a href="#kategori" class="hover:text-white transition">Lampu LED Hemat Energi</a></li>
                        <li><a href="#kategori" class="hover:text-white transition">MCB & Panel Listrik</a></li>
                        <li><a href="#kategori" class="hover:text-white transition">Stop Kontak & Saklar</a></li>
                        <li><a href="#kategori" class="hover:text-white transition">Pipa Conduit & Aksesoris</a></li>
                    </ul>
                </div>

                {{-- Col 4: Navigasi Cepat --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Navigasi</h4>
                    <ul class="space-y-2 text-xs">
                        <li><a href="#beranda" class="hover:text-white transition">Beranda</a></li>
                        <li><a href="#produk" class="hover:text-white transition">Produk Unggulan</a></li>
                        <li><a href="#keunggulan" class="hover:text-white transition">Keunggulan Toko</a></li>
                        <li><a href="#lokasi" class="hover:text-white transition">Lokasi Google Maps</a></li>
                        <li><a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="text-brand-orange hover:underline">Petunjuk Arah Rute</a></li>
                    </ul>
                </div>

                {{-- Col 5: Portal Staff --}}
                <div class="space-y-3">
                    <h4 class="text-xs font-bold text-white uppercase tracking-wider">Internal Staff</h4>
                    <p class="text-xs text-slate-400">Akses khusus kasir, admin gudang, dan owner toko.</p>
                    <div class="pt-1">
                        @auth
                            <a href="{{ auth()->user()->homeRoute() }}" class="inline-flex items-center gap-2 px-3 py-2 rounded-lg bg-emerald-600/30 border border-emerald-500/40 text-emerald-300 hover:text-white hover:bg-emerald-600 text-xs font-semibold transition">
                                <span>Buka Aplikasi ({{ auth()->user()->name }})</span>
                            </a>
                        @else
                            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-3.5 py-2 rounded-lg bg-white/10 hover:bg-white/20 text-white text-xs font-semibold border border-white/10 transition">
                                <svg class="w-3.5 h-3.5 text-brand-orange" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/></svg>
                                <span>Masuk Sistem (ERP)</span>
                            </a>
                        @endauth
                    </div>
                </div>

            </div>

            <div class="pt-8 border-t border-white/10 flex flex-col sm:flex-row items-center justify-between text-xs text-slate-500 gap-4">
                <p>&copy; {{ date('Y') }} Toko Listrik Berkat Jaya. Seluruh hak cipta dilindungi undang-undang.</p>
                <div class="flex items-center gap-4">
                    <a href="{{ $mapsUrl }}" target="_blank" rel="noopener noreferrer" class="hover:text-slate-300">Google Maps</a>
                    <span>•</span>
                    <a href="https://wa.me/{{ $whatsappNumber }}" target="_blank" rel="noopener noreferrer" class="hover:text-slate-300">WhatsApp Support</a>
                </div>
            </div>
        </div>
    </footer>

</body>
</html>
