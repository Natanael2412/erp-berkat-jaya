<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">
    <head>
        <meta charset="utf-8">
        <meta name="viewport" content="width=device-width, initial-scale=1">

        <title>ERP Berkat Jaya</title>

        <!-- Fonts -->
        <link rel="preconnect" href="https://fonts.bunny.net">
        <link href="https://fonts.bunny.net/css?family=figtree:400,500,600&display=swap" rel="stylesheet" />

        <!-- Styles / Scripts -->
        @if (file_exists(public_path('build/manifest.json')) || file_exists(public_path('hot')))
            @vite(['resources/css/app.css', 'resources/js/app.js'])
        @endif
    </head>
    <body class="font-sans antialiased bg-brand-base text-brand-dark min-h-screen flex flex-col justify-center items-center">
        <div class="max-w-xl mx-auto p-8 bg-white rounded-lg shadow-lg text-center border border-gray-100">
            <img src="{{ asset('images/logo.webp') }}" alt="ERP Berkat Jaya Logo" class="mx-auto w-48 h-auto mb-6">
            
            <h1 class="text-3xl font-bold text-brand-primary mb-2">ERP Berkat Jaya</h1>
            <p class="text-gray-600 mb-8">
                Sistem Informasi Akuntansi & POS Toko Listrik. 
                Platform terintegrasi untuk manajemen stok, kasir, dan akuntansi harian.
            </p>

            <div class="flex justify-center gap-4">
                @if (Route::has('login'))
                    @auth
                        <a href="{{ url('/dashboard') }}" class="px-6 py-2 bg-brand-primary text-white rounded-md font-semibold hover:bg-teal-700 transition">
                            Masuk ke Dashboard
                        </a>
                    @else
                        <a href="{{ route('login') }}" class="px-6 py-2 bg-brand-primary text-white rounded-md font-semibold hover:bg-teal-700 transition">
                            Login Kasir / Admin
                        </a>

                        @if (Route::has('register'))
                            <a href="{{ route('register') }}" class="px-6 py-2 bg-gray-100 text-gray-700 rounded-md font-semibold hover:bg-gray-200 transition">
                                Register
                            </a>
                        @endif
                    @endauth
                @endif
            </div>
        </div>
        
        <footer class="mt-12 text-sm text-gray-500">
            &copy; {{ date('Y') }} Berkat Jaya. All rights reserved.
        </footer>
    </body>
</html>
