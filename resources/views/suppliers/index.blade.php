<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="page-title">Data Supplier</h2>
                <p class="page-subtitle">Kelola daftar pemasok barang untuk kebutuhan toko.</p>
            </div>
            <a href="{{ route('suppliers.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Tambah Supplier
            </a>
        </div>
    </x-slot>

    <div class="card mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('suppliers.index') }}" class="flex flex-wrap gap-3 items-end w-full">
                <div class="flex-1 min-w-[250px]">
                    <label class="label">Pencarian</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Cari berdasarkan nama atau kode..." class="input">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Cari</button>
                    <a href="{{ route('suppliers.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Kode</th>
                        <th>Nama Supplier</th>
                        <th>No. Telepon</th>
                        <th>Alamat</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Total Transaksi PO</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($suppliers as $supplier)
                    <tr class="{{ !$supplier->is_active ? 'opacity-60 bg-slate-50' : '' }}">
                        <td class="font-mono font-bold">{{ $supplier->code }}</td>
                        <td class="font-bold text-gunmetal">{{ $supplier->name }}</td>
                        <td>{{ $supplier->phone ?? '-' }}</td>
                        <td>
                            <div class="text-xs text-slate-500 max-w-[200px] truncate" title="{{ $supplier->address }}">
                                {{ $supplier->address ?? '-' }}
                            </div>
                        </td>
                        <td class="text-center">
                            @if($supplier->is_active)
                                <span class="badge badge-green">Aktif</span>
                            @else
                                <span class="badge badge-gray">Non-Aktif</span>
                            @endif
                        </td>
                        <td class="num font-bold">{{ $supplier->purchase_orders_count }}</td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('suppliers.edit', $supplier) }}" class="text-sm font-semibold text-orange-600 hover:text-orange-800">Edit</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Tidak ada data supplier yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($suppliers->hasPages())
        <div class="border-t border-slate-200 p-4">
            {{ $suppliers->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
