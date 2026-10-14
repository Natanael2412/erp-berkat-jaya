<x-app-layout>
    <x-slot name="header">
        <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4">
            <div>
                <h2 class="page-title">Manajemen Pengguna</h2>
                <p class="page-subtitle">Kelola akses pengguna, kasir, admin, dan hak akses.</p>
            </div>
            <a href="{{ route('users.create') }}" class="btn btn-primary inline-flex">
                <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"></path></svg>
                Tambah Pengguna
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Nama Pengguna</th>
                        <th>Email & Kontak</th>
                        <th>Role Akses</th>
                        <th class="text-center">Status</th>
                        <th class="text-right w-24">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($users as $user)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="font-bold text-gunmetal">
                                {{ $user->name }}
                                @if($user->is(auth()->user()))
                                    <span class="badge badge-teal ml-2">Anda</span>
                                @endif
                            </td>
                            <td class="text-slate-600">
                                <a href="mailto:{{ $user->email }}" class="hover:text-teal-600 transition-colors">{{ $user->email }}</a>
                            </td>
                            <td>
                                @if($user->role === 'owner')
                                    <span class="badge badge-gunmetal"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path></svg>{{ $user->roleLabel() }}</span>
                                @elseif($user->role === 'admin')
                                    <span class="badge badge-teal">{{ $user->roleLabel() }}</span>
                                @else
                                    <span class="badge badge-blue">{{ $user->roleLabel() }}</span>
                                @endif
                            </td>
                            <td class="text-center">
                                @if($user->is_active)
                                    <span class="badge badge-green">Aktif</span>
                                @else
                                    <span class="badge badge-red">Nonaktif</span>
                                @endif
                            </td>
                            <td class="text-right">
                                <a href="{{ route('users.edit', $user) }}" class="btn btn-ghost text-teal-600 hover:bg-teal-50 px-3 py-1.5 h-auto">
                                    Edit
                                </a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-8 text-center text-slate-500">Belum ada pengguna yang terdaftar.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
