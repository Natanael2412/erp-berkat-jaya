<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Terima Barang · <span class="font-mono text-teal-700">{{ $po->po_number }}</span></h2>
                <p class="page-subtitle">{{ $po->supplier->name }} · isi jumlah yang benar-benar datang dan yang ditolak (cacat/salah).</p>
            </div>
            <a href="{{ route('purchase-orders.show', $po) }}" class="btn btn-secondary">Batal &amp; Kembali</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('purchase-orders.receive', $po) }}" class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="card lg:col-span-2">
            <div class="card-header"><h3 class="card-title">Barang Datang</h3></div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>Produk</th>
                            <th class="text-right">Dipesan</th>
                            <th class="text-right">Sudah Diterima</th>
                            <th class="text-right">Sisa</th>
                            <th class="text-right bg-emerald-50">Datang Sekarang</th>
                            <th class="text-right bg-red-50">Ditolak</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($po->items as $item)
                            @php $remaining = $item->qtyRemaining(); @endphp
                            <tr>
                                <td>
                                    <div class="font-bold">{{ $item->productUnit->product->name }}</div>
                                    <div class="text-xs text-slate-500">{{ rp($item->unit_price) }} / {{ $item->productUnit->unit->name }}</div>
                                </td>
                                <td class="num">{{ num($item->qty_ordered) }} <span class="text-xs text-slate-500">{{ $item->productUnit->unit->name }}</span></td>
                                <td class="num text-emerald-700">{{ num($item->qtyAccepted()) }}</td>
                                <td class="num font-bold">{{ num($remaining) }}</td>
                                @if ($remaining > 0)
                                    <td class="bg-emerald-50/60">
                                        <input type="number" step="0.0001" min="0" name="items[{{ $item->id }}][qty_received]"
                                               value="{{ old("items.{$item->id}.qty_received", $remaining) }}" class="input num w-28 text-right">
                                    </td>
                                    <td class="bg-red-50/60">
                                        <input type="number" step="0.0001" min="0" name="items[{{ $item->id }}][qty_rejected]"
                                               value="{{ old("items.{$item->id}.qty_rejected", 0) }}" class="input num w-24 text-right">
                                    </td>
                                @else
                                    <td class="bg-emerald-50/60 text-center text-xs text-slate-400">Lengkap</td>
                                    <td class="bg-red-50/60 text-center text-slate-300">—</td>
                                    <input type="hidden" name="items[{{ $item->id }}][qty_received]" value="0">
                                    <input type="hidden" name="items[{{ $item->id }}][qty_rejected]" value="0">
                                @endif
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
            <div class="border-t border-slate-200 p-4 text-xs text-slate-500">
                “Datang” adalah total fisik yang tiba (termasuk yang ditolak). Hanya <strong>datang − ditolak</strong> yang masuk stok dan menjadi utang.
            </div>
        </div>

        <div>
            <div class="card sticky top-24">
                <div class="card-header"><h3 class="card-title">Dokumen Penerimaan</h3></div>
                <div class="card-body space-y-4">
                    <div>
                        <label class="label" for="receipt_date">Tanggal Terima <span class="text-red-500">*</span></label>
                        <input id="receipt_date" type="date" name="receipt_date"
                               min="{{ $po->order_date->toDateString() }}" max="{{ today()->toDateString() }}"
                               value="{{ old('receipt_date', today()->toDateString()) }}" class="input" required>
                        @error('receipt_date') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="notes">Catatan</label>
                        <input id="notes" type="text" name="notes" maxlength="255" value="{{ old('notes') }}" class="input" placeholder="Mis. 1 roll terkelupas, ditolak">
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-full" id="btn-save-receipt">Simpan Penerimaan</button>
                    <p class="hint">Stok, HPP rata-rata, dan jurnal Persediaan (D) / Utang Usaha (K) diperbarui otomatis.</p>
                </div>
            </div>
        </div>
    </form>
</x-app-layout>
