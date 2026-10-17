<x-guest-layout>
    <div class="mb-5">
        <h1 class="text-xl font-extrabold tracking-tight text-brand-ink">Masuk ke Sistem</h1>
        <p class="mt-1 text-sm text-slate-500">Gunakan akun yang diberikan oleh Owner toko.</p>
    </div>

    <!-- Session Status -->
    <x-auth-session-status class="mb-4" :status="session('status')" />

    <form method="POST" action="{{ route('login') }}">
        @csrf

        <!-- Email Address -->
        <div>
            <x-input-label for="email" value="Email" />
            <x-text-input id="email" class="block mt-1 w-full" type="email" name="email" :value="old('email')" required autofocus autocomplete="username" placeholder="nama@toko.com" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" value="Kata Sandi" />

            <x-text-input id="password" class="block mt-1 w-full"
                            type="password"
                            name="password"
                            required autocomplete="current-password" placeholder="••••••••" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Remember Me -->
        <div class="block mt-4">
            <label for="remember_me" class="inline-flex items-center">
                <input id="remember_me" type="checkbox" class="rounded border-slate-300 text-brand-teal shadow-sm focus:ring-brand-teal" name="remember">
                <span class="ms-2 text-sm text-slate-600">Ingat saya di perangkat ini</span>
            </label>
        </div>

        <x-primary-button class="mt-6 w-full">
            Masuk
        </x-primary-button>

        <p class="mt-4 text-center text-xs text-slate-500">
            Lupa kata sandi? Hubungi Owner toko untuk mengatur ulang akun Anda.
        </p>
    </form>
</x-guest-layout>
