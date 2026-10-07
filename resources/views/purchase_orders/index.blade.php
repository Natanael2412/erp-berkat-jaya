<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Purchase Orders') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-bold">Daftar Purchase Order</h3>
                    <a href="{{ route('purchase-orders.create') }}" class="px-4 py-2 bg-brand-primary text-white rounded">(+) Buat PO Baru</a>
                </div>
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 border-b">ID PO</th>
                            <th class="p-3 border-b">Supplier</th>
                            <th class="p-3 border-b">Total</th>
                            <th class="p-3 border-b">Status</th>
                            <th class="p-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($pos as $po)
                        <tr>
                            <td class="p-3 border-b">#{{ $po->id }}</td>
                            <td class="p-3 border-b">{{ $po->supplier_name }}</td>
                            <td class="p-3 border-b">Rp {{ number_format($po->total_amount, 0, ',', '.') }}</td>
                            <td class="p-3 border-b">
                                <span class="px-2 py-1 text-sm rounded bg-gray-200">{{ strtoupper($po->status) }}</span>
                            </td>
                            <td class="p-3 border-b">
                                <a href="{{ route('purchase-orders.show', $po->id) }}" class="text-blue-500">Lihat / Terima Barang</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">Belum ada data PO.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
