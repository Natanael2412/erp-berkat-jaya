<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Data Produk') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <div class="flex justify-between mb-4">
                    <h3 class="text-lg font-bold">Daftar Produk & Stok</h3>
                    <a href="{{ route('products.create') }}" class="px-4 py-2 bg-brand-primary text-white rounded">(+) Tambah Produk</a>
                </div>
                
                <table class="w-full text-left border-collapse">
                    <thead>
                        <tr class="bg-gray-100">
                            <th class="p-3 border-b">SKU</th>
                            <th class="p-3 border-b">Nama Produk</th>
                            <th class="p-3 border-b">Stok (Base Unit)</th>
                            <th class="p-3 border-b">HPP Saat Ini</th>
                            <th class="p-3 border-b">Aksi</th>
                        </tr>
                    </thead>
                    <tbody>
                        @forelse($products as $product)
                        <tr>
                            <td class="p-3 border-b">{{ $product->sku }}</td>
                            <td class="p-3 border-b">{{ $product->name }}</td>
                            <td class="p-3 border-b">{{ $product->current_stock }} {{ $product->base_unit }}</td>
                            <td class="p-3 border-b">Rp {{ number_format($product->current_hpp, 0, ',', '.') }}</td>
                            <td class="p-3 border-b">
                                <a href="{{ route('products.edit', $product->id) }}" class="text-blue-500">Edit</a>
                            </td>
                        </tr>
                        @empty
                        <tr>
                            <td colspan="5" class="p-3 text-center text-gray-500">Belum ada data produk.</td>
                        </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
