<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Penerimaan Barang PO #') . $purchaseOrder->id }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6 mb-6">
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-4">
                    <div>
                        <span class="block text-xs text-gray-500 font-bold uppercase">Supplier</span>
                        <span class="text-lg">{{ $purchaseOrder->supplier_name }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 font-bold uppercase">Tanggal Dibuat</span>
                        <span class="text-lg">{{ $purchaseOrder->created_at->format('d M Y') }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 font-bold uppercase">Status</span>
                        <span class="px-2 py-1 rounded font-bold text-sm bg-gray-200">{{ strtoupper($purchaseOrder->status) }}</span>
                    </div>
                    <div>
                        <span class="block text-xs text-gray-500 font-bold uppercase">Total Tagihan</span>
                        <span class="text-lg font-bold text-brand-primary">Rp {{ number_format($purchaseOrder->total_amount, 0, ',', '.') }}</span>
                    </div>
                </div>
            </div>

            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg p-6">
                <h3 class="text-lg font-bold mb-4">Detail Item & Form Penerimaan</h3>
                
                @if (session('success'))
                    <div class="mb-4 bg-green-100 border border-green-400 text-green-700 px-4 py-3 rounded">
                        {{ session('success') }}
                    </div>
                @endif

                @if ($purchaseOrder->status === 'komplit')
                    <div class="mb-4 bg-blue-100 border border-blue-400 text-blue-700 px-4 py-3 rounded">
                        Semua barang dalam PO ini telah diterima secara penuh.
                    </div>
                @endif

                <form method="POST" action="{{ route('purchase-orders.receive', $purchaseOrder->id) }}">
                    @csrf
                    
                    <div class="overflow-x-auto">
                        <table class="w-full text-left border-collapse min-w-max">
                            <thead>
                                <tr class="bg-gray-100">
                                    <th class="p-3 border-b">Produk</th>
                                    <th class="p-3 border-b text-center">Pesan</th>
                                    <th class="p-3 border-b text-center">Telah Diterima</th>
                                    <th class="p-3 border-b text-center">Retur Sblmnya</th>
                                    <th class="p-3 border-b bg-yellow-50 text-center border-l">Terima (Sekarang)</th>
                                    <th class="p-3 border-b bg-red-50 text-center">Retur (Sekarang)</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($purchaseOrder->items as $item)
                                    @php 
                                        $sisa = $item->qty_ordered - $item->qty_received; 
                                        $isComplete = $sisa <= 0;
                                    @endphp
                                <tr>
                                    <td class="p-3 border-b">
                                        <div class="font-bold">{{ $item->product->name }}</div>
                                        <div class="text-xs text-gray-500">Harga: Rp {{ number_format($item->unit_price, 0, ',', '.') }} / {{ $item->uom->unit_name ?? 'Base' }}</div>
                                    </td>
                                    <td class="p-3 border-b text-center font-bold">{{ $item->qty_ordered }} {{ $item->uom->unit_name ?? '' }}</td>
                                    <td class="p-3 border-b text-center text-green-600 font-bold">{{ $item->qty_received }}</td>
                                    <td class="p-3 border-b text-center text-red-600">{{ $item->qty_retur }}</td>
                                    
                                    <td class="p-3 border-b bg-yellow-50 border-l">
                                        @if(!$isComplete)
                                            <input type="number" step="0.01" name="items[{{ $item->id }}][qty_received]" value="{{ $sisa }}" max="{{ $sisa }}" min="0" class="w-24 text-center border-gray-300 rounded focus:ring-brand-primary mx-auto block">
                                        @else
                                            <div class="text-center text-gray-400">Selesai</div>
                                            <input type="hidden" name="items[{{ $item->id }}][qty_received]" value="0">
                                        @endif
                                    </td>
                                    <td class="p-3 border-b bg-red-50">
                                        @if(!$isComplete)
                                            <input type="number" step="0.01" name="items[{{ $item->id }}][qty_retur]" value="0" min="0" class="w-24 text-center border-gray-300 rounded mx-auto block">
                                        @else
                                            <div class="text-center text-gray-400">-</div>
                                            <input type="hidden" name="items[{{ $item->id }}][qty_retur]" value="0">
                                        @endif
                                    </td>
                                </tr>
                                @endforeach
                            </tbody>
                        </table>
                    </div>

                    @if ($purchaseOrder->status !== 'komplit')
                        <div class="mt-6 flex justify-end">
                            <button type="submit" class="px-6 py-3 bg-brand-primary text-white rounded font-bold hover:bg-teal-700">Simpan Penerimaan Barang</button>
                        </div>
                    @endif
                </form>
            </div>
        </div>
    </div>
</x-app-layout>
