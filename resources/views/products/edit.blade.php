<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Edit Produk: ') . $product->name }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="productForm()">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                
                @if ($errors->any())
                    <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
                        <strong>Terjadi Kesalahan!</strong>
                        <ul class="mt-2 list-disc list-inside text-sm">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('products.update', $product->id) }}">
                    @csrf
                    @method('PUT')
                    
                    <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">SKU</label>
                            <input type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="w-full border-gray-300 rounded focus:ring-brand-primary" required>
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-700 mb-1">Nama Produk</label>
                            <input type="text" name="name" value="{{ old('name', $product->name) }}" class="w-full border-gray-300 rounded focus:ring-brand-primary" required>
                        </div>
                        <div class="md:col-span-2">
                            <label class="block text-sm font-medium text-gray-700 mb-1">Satuan Dasar (Base Unit) e.g., Pcs, Meter</label>
                            <input type="text" name="base_unit" value="{{ old('base_unit', $product->base_unit) }}" class="w-full border-gray-300 rounded focus:ring-brand-primary" required>
                        </div>
                    </div>

                    <div class="border-t pt-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold">Konversi Satuan (UoM)</h3>
                            <button type="button" @click="addConversion" class="text-sm bg-gray-200 hover:bg-gray-300 px-3 py-1 rounded">+ Tambah Satuan</button>
                        </div>
                        
                        <template x-for="(conv, index) in conversions" :key="index">
                            <div class="flex gap-4 items-end mb-4 bg-gray-50 p-4 rounded border">
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Nama Satuan (misal: Dus, Roll)</label>
                                    <input type="text" :name="`conversions[${index}][unit_name]`" x-model="conv.unit_name" class="w-full text-sm border-gray-300 rounded" required>
                                </div>
                                <div class="flex-1">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Multiplier (Berapa Base Unit?)</label>
                                    <input type="number" step="0.01" :name="`conversions[${index}][multiplier]`" x-model="conv.multiplier" class="w-full text-sm border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <button type="button" @click="removeConversion(index)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm">Hapus</button>
                                </div>
                            </div>
                        </template>
                        
                        <p x-show="conversions.length === 0" class="text-gray-500 text-sm">Tidak ada konversi satuan. Barang ini hanya akan menggunakan satuan dasarnya.</p>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('products.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">Batal</a>
                        <button type="submit" class="px-4 py-2 bg-brand-primary text-white rounded font-bold hover:bg-teal-700">Simpan Perubahan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function productForm() {
            return {
                conversions: @json($product->uomConversions->map(function($uom) {
                    return ['unit_name' => $uom->unit_name, 'multiplier' => $uom->multiplier];
                })),
                addConversion() {
                    this.conversions.push({ unit_name: '', multiplier: 1 });
                },
                removeConversion(index) {
                    this.conversions.splice(index, 1);
                }
            }
        }
    </script>
</x-app-layout>
