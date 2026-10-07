<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Buat Purchase Order (PO)') }}
        </h2>
    </x-slot>

    <div class="py-12" x-data="poForm()">
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

                <form method="POST" action="{{ route('purchase-orders.store') }}">
                    @csrf
                    
                    <div class="mb-6">
                        <label class="block text-sm font-medium text-gray-700 mb-1">Nama Supplier</label>
                        <input type="text" name="supplier_name" value="{{ old('supplier_name') }}" class="w-full border-gray-300 rounded focus:ring-brand-primary" required>
                    </div>

                    <div class="border-t pt-6 mb-6">
                        <div class="flex justify-between items-center mb-4">
                            <h3 class="text-lg font-bold">Item PO</h3>
                            <button type="button" @click="addItem" class="text-sm bg-brand-primary text-white px-3 py-1 rounded">+ Tambah Item</button>
                        </div>
                        
                        <template x-for="(item, index) in items" :key="index">
                            <div class="flex flex-wrap gap-4 items-end mb-4 bg-gray-50 p-4 rounded border">
                                <div class="w-full md:w-1/3">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Produk</label>
                                    <select :name="`items[${index}][product_id]`" x-model="item.product_id" @change="updateUoms(index)" class="w-full text-sm border-gray-300 rounded" required>
                                        <option value="">Pilih Produk</option>
                                        <template x-for="prod in productsList" :key="prod.id">
                                            <option :value="prod.id" x-text="prod.name"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-full md:flex-1">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Satuan Beli (UoM)</label>
                                    <select :name="`items[${index}][uom_id]`" x-model="item.uom_id" class="w-full text-sm border-gray-300 rounded" required>
                                        <option value="">Pilih Satuan</option>
                                        <template x-for="uom in getUomsForProduct(item.product_id)" :key="uom.id">
                                            <option :value="uom.id" x-text="uom.unit_name + ' (' + uom.multiplier + ' base unit)'"></option>
                                        </template>
                                    </select>
                                </div>
                                <div class="w-full md:flex-1">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Qty Pesan</label>
                                    <input type="number" step="0.01" :name="`items[${index}][qty_ordered]`" x-model="item.qty_ordered" class="w-full text-sm border-gray-300 rounded" required>
                                </div>
                                <div class="w-full md:flex-1">
                                    <label class="block text-xs font-bold text-gray-700 mb-1">Harga Beli / Satuan</label>
                                    <input type="number" step="0.01" :name="`items[${index}][unit_price]`" x-model="item.unit_price" class="w-full text-sm border-gray-300 rounded" required>
                                </div>
                                <div>
                                    <button type="button" @click="removeItem(index)" class="bg-red-500 hover:bg-red-600 text-white px-3 py-2 rounded text-sm w-full md:w-auto">Hapus</button>
                                </div>
                            </div>
                        </template>
                        
                        <p x-show="items.length === 0" class="text-red-500 text-sm font-bold">Minimal harus ada 1 item yang dipesan!</p>
                    </div>

                    <div class="flex justify-between items-center bg-gray-100 p-4 rounded mb-6">
                        <span class="font-bold text-lg">Estimasi Total PO:</span>
                        <span class="font-black text-2xl text-brand-primary">Rp <span x-text="calculateTotal()"></span></span>
                    </div>

                    <div class="flex justify-end gap-3">
                        <a href="{{ route('purchase-orders.index') }}" class="px-4 py-2 bg-gray-300 text-gray-800 rounded">Batal</a>
                        <button type="submit" :disabled="items.length === 0" class="px-4 py-2 bg-brand-primary text-white rounded font-bold hover:bg-teal-700 disabled:opacity-50">Buat Purchase Order</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <script>
        function poForm() {
            return {
                productsList: @json($products),
                items: [
                    { product_id: '', uom_id: '', qty_ordered: 1, unit_price: 0 }
                ],
                
                addItem() {
                    this.items.push({ product_id: '', uom_id: '', qty_ordered: 1, unit_price: 0 });
                },
                
                removeItem(index) {
                    this.items.splice(index, 1);
                },
                
                getUomsForProduct(productId) {
                    if (!productId) return [];
                    const product = this.productsList.find(p => p.id == productId);
                    return product ? product.uom_conversions : [];
                },
                
                updateUoms(index) {
                    this.items[index].uom_id = '';
                },
                
                calculateTotal() {
                    let total = 0;
                    this.items.forEach(item => {
                        total += (parseFloat(item.qty_ordered) || 0) * (parseFloat(item.unit_price) || 0);
                    });
                    return new Intl.NumberFormat('id-ID').format(total);
                }
            }
        }
    </script>
</x-app-layout>
