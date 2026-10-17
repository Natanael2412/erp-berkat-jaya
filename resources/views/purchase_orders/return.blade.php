<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Retur ke Supplier · <span class="font-mono text-teal-700">{{ $po->po_number }}</span></h2>
                <p class="page-subtitle">{{ $po->supplier->name }} · hanya barang yang sudah diterima yang bisa diretur.</p>
            </div>
            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary">Batal &amp; Kembali</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('purchase-orders.return', $po) }}" class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="card lg:col-span-2">
            <div class="card-header"><h3 class="card-title">Barang yang Diretur</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-right">Diterima</th>
                            <th class="text-right">Sudah Diretur</th>
                            <th class="text-right">Bisa Diretur</th>
                            <th class="text-right bg-orange-50">Retur Sekarang</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po->items as $item)
                            @php $returnable = $item->qtyReturnable(); @endphp
                            <tr>
                                <td>
                                    <div class="font-bold">{{ $item->productUnit->product->name }}</div>
                                    <div class="text-xs text-slate-500">{{ rp($item->unit_price) }} / {{ $item->productUnit->unit->name }}</div>
                                </td>
                                <td class="num">{{ num($item->qtyAccepted()) }}</td>
                                <td class="num text-orange-600">{{ num($item->qtyReturned()) }}</td>
                                <td class="num font-bold">{{ num($returnable) }} <span class="text-xs font-medium text-slate-500">{{ $item->productUnit->unit->name }}</span></td>
                                <td class="bg-orange-50/60">
                                    @if ($returnable > 0)
                                        <input type="number" step="0.0001" min="0" max="{{ $returnable }}" name="items[{{ $item->id }}]"
                                               value="{{ old("items.{$item->id}", 0) }}" class="input num w-28 text-right">
                                    @else
                                        <span class="block text-center text-slate-300">—</span>
                                        <input type="hidden" name="items[{{ $item->id }}]" value="0">
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        <div>
            <div class="card sticky top-24">
                <div class="card-header"><h3 class="card-title">Dokumen Retur</h3></div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label" for="return_date">Tanggal Retur <span class="text-red-500">*</span></label>
                        <input id="return_date" type="date" name="return_date" max="{{ today()->toDateString() }}" value="{{ old('return_date', today()->toDateString()) }}" class="input" required>
                        @error('return_date') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="reason">Alasan Retur <span class="text-red-500">*</span></label>
                        <input id="reason" type="text" name="reason" maxlength="255" value="{{ old('reason') }}" class="input" required placeholder="Mis. salah tipe / cacat produksi">
                        @error('reason') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <button type="submit" class="btn btn-warning btn-lg w-full" id="btn-save-return" onclick="return confirm('Simpan retur? Stok dan utang akan dikurangi.');">Simpan Retur</button>
                    <p class="hint">Stok keluar sebesar harga beli, utang supplier berkurang, dan jurnal dibuat otomatis.</p>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
