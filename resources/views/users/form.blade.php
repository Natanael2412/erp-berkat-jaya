<x-app-layout>
    <x-slot name="header">
        <div class="flex items-center gap-4">
            <a href="{{ route('users.index') }}" class="p-2 -ml-2 rounded-lg hover:bg-slate-200 text-slate-500 transition-colors">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"></path></svg>
            </a>
            <div>
                <h2 class="page-title">{{ $user->exists ? 'Edit Pengguna: ' . $user->name : 'Tambah Pengguna Baru' }}</h2>
                <p class="page-subtitle">Atur profil, password, dan hak akses pengguna ke sistem.</p>
            </div>
        </div>
    </x-slot>

    <div class="card max-w-2xl">
        <form method="POST" action="{{ $user->exists ? route('users.update', $user) : route('users.store') }}">
            @csrf
            @if($user->exists)
                @method('PUT')
            @endif

            <div class="card-body space-y-6">
                <!-- Nama -->
                <div>
                    <label for="name" class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                    <input type="text" id="name" name="name" value="{{ old('name', $user->name) }}" class="input" required maxlength="100">
                    @error('name') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Email -->
                <div>
                    <label for="email" class="label">Alamat Email <span class="text-red-500">*</span></label>
                    <input type="email" id="email" name="email" value="{{ old('email', $user->email) }}" class="input" required maxlength="150" autocomplete="off">
                    @error('email') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>

                <!-- Role -->
                <div>
                    <label for="role" class="label">Role Akses <span class="text-red-500">*</span></label>
                    <select id="role" name="role" class="input" required>
                        @foreach(App\Models\User::ROLES as $key => $label)
                            <option value="{{ $key }}" {{ old('role', $user->role) === $key ? 'selected' : '' }}>
                                {{ $label }}
                            </option>
                        @endforeach
                    </select>
                    @error('role') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                    <p class="text-xs text-slate-500 mt-2">
                        <strong>Owner:</strong> Akses penuh. <strong>Admin:</strong> Operasional & Gudang. <strong>Kasir:</strong> Hanya akses POS/Kasir.
                    </p>
                </div>

                <hr class="border-slate-200">

                <div class="bg-slate-50 p-4 rounded-lg border border-slate-200">
                    <h3 class="text-sm font-bold text-gunmetal mb-4">{{ $user->exists ? 'Ubah Password (Kosongkan jika tidak ingin mengubah)' : 'Buat Password' }}</h3>
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                        <!-- Password -->
                        <div>
                            <label for="password" class="label">Password {{ !$user->exists ? '<span class="text-red-500">*</span>' : '' }}</label>
                            <input type="password" id="password" name="password" class="input" {{ !$user->exists ? 'required' : '' }} autocomplete="new-password">
                            @error('password') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                        </div>

                        <!-- Konfirmasi Password -->
                        <div>
                            <label for="password_confirmation" class="label">Konfirmasi Password {{ !$user->exists ? '<span class="text-red-500">*</span>' : '' }}</label>
                            <input type="password" id="password_confirmation" name="password_confirmation" class="input" {{ !$user->exists ? 'required' : '' }} autocomplete="new-password">
                        </div>
                    </div>
                </div>

                <!-- Status Aktif -->
                @if($user->exists && !$user->is(auth()->user()))
                <div>
                    <label class="flex items-center gap-3 p-3 border border-slate-200 rounded-lg cursor-pointer hover:bg-slate-50 transition-colors">
                        <input type="checkbox" name="is_active" value="1" {{ old('is_active', $user->is_active) ? 'checked' : '' }} class="w-5 h-5 text-teal-600 border-slate-300 rounded focus:ring-teal-500">
                        <div>
                            <div class="font-bold text-gunmetal">Akun Aktif</div>
                            <div class="text-sm text-slate-500">Pengguna dapat login dan menggunakan sistem.</div>
                        </div>
                    </label>
                    @error('is_active') <p class="text-red-500 text-sm mt-1">{{ $message }}</p> @enderror
                </div>
                @endif
                
                @if($user->exists && $user->is(auth()->user()))
                    <input type="hidden" name="is_active" value="1">
                    <div class="bg-blue-50 text-blue-800 p-3 rounded text-sm border border-blue-100 flex items-start gap-3">
                        <svg class="w-5 h-5 mt-0.5 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                        Anda sedang mengedit akun Anda sendiri. Anda tidak bisa menonaktifkan akun atau menurunkan role Anda sendiri.
                    </div>
                @endif
            </div>

            <div class="card-footer bg-slate-50 flex justify-end gap-3">
                <a href="{{ route('users.index') }}" class="btn btn-ghost">Batal</a>
                <button type="submit" class="btn btn-primary">
                    {{ $user->exists ? 'Simpan Perubahan' : 'Tambah Pengguna' }}
                </button>
            </div>
        </form>
    </div>
</x-app-layout>
