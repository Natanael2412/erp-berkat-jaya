<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="page-title">Detail Transaksi #{{ $sale->receipt_no ?? $sale->invoice_number }}</h2>
                <p class="page-subtitle">Rincian item terjual dan jurnal akuntansi terkait.</p>
            </div>
            <div class="flex gap-3">
                <a href="{{ route('sales.invoice', $sale) }}" target="_blank" class="btn btn-dark">🖨️ Cetak Invoice A4</a>
                <a href="{{ route('sales.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Kolom Kiri: Info Transaksi -->
        <div class="lg:col-span-1 space-y-6">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Informasi Umum</h3>
                    @if($sale->status === 'completed')
                        <span class="badge badge-green">Selesai</span>
                    @elseif($sale->status === 'voided')
                        <span class="badge badge-red">Void</span>
                    @endif
                </div>
                <div class="card-body space-y-4">
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wide font-bold">Waktu Transaksi</div>
                        <div class="font-medium mt-1">{{ $sale->created_at->format('d/m/Y H:i:s') }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wide font-bold">Kasir</div>
                        <div class="font-medium mt-1">{{ $sale->cashier->name }}</div>
                    </div>
                    <div>
                        <div class="text-xs text-slate-500 uppercase tracking-wide font-bold">Metode Pembayaran</div>
                        <div class="font-medium mt-1">{{ $sale->payment_method }}</div>
                    </div>
                    @if($sale->status === 'voided')
                    <div class="bg-red-50 p-3 rounded border border-red-200 mt-4">
                        <div class="text-xs text-red-600 uppercase tracking-wide font-bold mb-1">Dibatalkan Oleh</div>
                        <div class="font-medium text-red-800">{{ $sale->voider->name ?? '-' }}</div>
                        <div class="text-xs text-red-600 uppercase tracking-wide font-bold mt-2 mb-1">Alasan Void</div>
                        <div class="text-sm text-red-800 italic">"{{ $sale->void_reason }}"</div>
                    </div>
                    @endif
                </div>
            </div>

            <!-- Jurnal Terkait -->
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Jurnal Akuntansi (Buku Besar)</h3>
                </div>
                <div class="card-body p-0">
                    <div class="table-wrap">
                        <table class="table text-xs">
                            <thead class="bg-slate-50">
                                <tr>
                                    <th>Akun</th>
                                    <th class="text-right">Debit</th>
                                    <th class="text-right">Kredit</th>
                                </tr>
                            </thead>
                            <tbody>
                                @foreach($sale->journals as $journal)
                                    @foreach($journal->lines as $line)
                                    <tr>
                                        <td>{{ $line->account->code }} - {{ $line->account->name }}</td>
                                        <td class="text-right {{ $line->debit > 0 ? 'font-bold text-gunmetal' : 'text-slate-400' }}">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}</td>
                                        <td class="text-right {{ $line->credit > 0 ? 'font-bold text-gunmetal' : 'text-slate-400' }}">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}</td>
                                    </tr>
                                    @endforeach
                                @endforeach
                            </tbody>
                        </table>
                    </div>
                </div>
            </div>
            
            @if(auth()->user()->isOwner() && $sale->status === 'completed')
            <div class="card border-red-200">
                <div class="card-body">
                    <h3 class="text-red-700 font-bold mb-2">Batalkan (Void) Transaksi</h3>
                    <p class="text-xs text-slate-500 mb-4">Membatalkan penjualan akan mengembalikan stok barang dan membuat jurnal pembalik secara otomatis.</p>
                    
                    <form action="{{ route('sales.void', $sale) }}" method="POST" onsubmit="return confirm('Apakah Anda yakin ingin membatalkan transaksi ini? Tindakan ini tidak dapat diurungkan.');">
                        @csrf
                        <div class="mb-3">
                            <label class="label">Alasan Void</label>
                            <input type="text" name="void_reason" required class="input" placeholder="Contoh: Salah input kasir">
                        </div>
                        <button type="submit" class="btn btn-danger w-full">Void Transaksi</button>
                    </form>
                </div>
            </div>
            @endif
        </div>

        <!-- Kolom Kanan: Rincian Item -->
        <div class="lg:col-span-2">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Rincian Item (Keranjang)</h3>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th>Satuan</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Qty</th>
                                <th class="text-right">Subtotal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($sale->items as $item)
                            <tr>
                                <td>
                                    <div class="font-bold">{{ $item->productUnit->product->name ?? 'Produk Dihapus' }}</div>
                                    <div class="text-xs text-slate-500">{{ $item->productUnit->product->sku ?? '-' }}</div>
                                </td>
                                <td>{{ $item->productUnit->unit->name ?? '-' }}</td>
                                <td class="num">Rp {{ number_format($item->unit_price, 0, ',', '.') }}</td>
                                <td class="num">{{ num($item->qty) }}</td>
                                <td class="num font-bold text-teal-700">Rp {{ number_format($item->qty * $item->unit_price, 0, ',', '.') }}</td>
                            </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="4" class="text-right uppercase tracking-wider text-slate-500 text-xs">Total Pembayaran</td>
                                <td class="num text-lg text-emerald-600 font-black">Rp {{ number_format($sale->total(), 0, ',', '.') }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
