<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Invoice {{ $sale->invoice_number }}</title>
    @vite(['resources/css/app.css'])
    <style>
        @media print {
            body { font-size: 11pt; color: black; background: white; }
            .no-print { display: none !important; }
            .print-border { border-color: #000 !important; }
            @page { margin: 1cm; size: A4; }
        }
        body { font-family: 'Inter', sans-serif; background: #f1f5f9; color: #1e293b; }
        .invoice-box {
            max-width: 21cm; /* Lebar kertas A4 */
            min-height: 29.7cm; /* Tinggi kertas A4 */
            margin: 2rem auto;
            padding: 2cm;
            background: white;
            box-shadow: 0 4px 6px -1px rgb(0 0 0 / 0.1);
        }
        @media print {
            .invoice-box { margin: 0; box-shadow: none; min-height: auto; padding: 0; }
        }
    </style>
</head>
<body>

    <!-- Tombol Navigasi (Sembunyi saat print) -->
    <div class="no-print text-center py-4 bg-gunmetal text-white sticky top-0 shadow-md">
        <div class="space-x-4">
            <button onclick="window.print()" class="px-6 py-2 bg-teal-600 hover:bg-teal-700 text-white font-bold rounded shadow">🖨️ Cetak Invoice (A4)</button>
            <a href="{{ route('sales.show', $sale) }}" class="px-6 py-2 bg-slate-700 hover:bg-slate-600 text-white font-bold rounded shadow">Kembali ke Detail</a>
        </div>
        <p class="text-xs text-slate-400 mt-2">Pastikan pengaturan printer mematikan "Headers and footers" dan mengatur Margin ke "Default" atau "None".</p>
    </div>

    <!-- Area Kertas A4 -->
    <div class="invoice-box text-sm">
        <!-- HEADER -->
        <div class="flex justify-between items-start mb-10">
            <div>
                <h1 class="text-4xl font-black text-gunmetal tracking-tighter uppercase mb-1">INVOICE</h1>
                <p class="text-slate-500 font-medium"># {{ $sale->invoice_number }}</p>
                <div class="mt-4">
                    <p class="font-bold text-gunmetal">Tanggal Transaksi:</p>
                    <p>{{ \Carbon\Carbon::parse($sale->sale_date)->translatedFormat('d F Y H:i') }}</p>
                </div>
                <div class="mt-2">
                    <p class="font-bold text-gunmetal">Metode Pembayaran:</p>
                    <p class="uppercase">{{ $sale->payment_method }}</p>
                </div>
            </div>
            <div class="text-right">
                <!-- Gunakan text biasa agar lebih ringan untuk di-print -->
                <h2 class="text-2xl font-black tracking-widest text-teal-800 uppercase">BERKAT JAYA</h2>
                <p class="text-xs font-bold tracking-[0.2em] text-gunmetal uppercase border-b-2 border-teal-600 pb-2 inline-block mb-2">Toko Alat Listrik</p>
                <p class="text-slate-600">Jl. Raya Elektronik No. 123</p>
                <p class="text-slate-600">Kota Teknologi, 12345</p>
                <p class="text-slate-600">Telp: (021) 555-0198</p>
                <p class="text-slate-600">Email: info@berkatjaya.com</p>
            </div>
        </div>

        <!-- INFO KEPADA -->
        <div class="mb-8 p-4 bg-slate-50 rounded border border-slate-200 print-border">
            <p class="text-xs font-bold text-slate-400 uppercase tracking-wider mb-1">Kepada Yth:</p>
            <p class="text-lg font-bold text-gunmetal">Pelanggan Cash / Proyek</p>
            <p class="text-slate-600">Terima kasih telah berbelanja di Berkat Jaya.</p>
        </div>

        <!-- TABEL BARANG -->
        <table class="w-full mb-8 text-left border-collapse">
            <thead>
                <tr class="bg-gunmetal text-white">
                    <th class="py-2 px-3 font-bold w-12 text-center rounded-tl">No</th>
                    <th class="py-2 px-3 font-bold">Deskripsi Barang</th>
                    <th class="py-2 px-3 font-bold text-center w-24">Qty</th>
                    <th class="py-2 px-3 font-bold text-right w-32">Harga Satuan</th>
                    <th class="py-2 px-3 font-bold text-right w-36 rounded-tr">Subtotal</th>
                </tr>
            </thead>
            <tbody>
                @foreach($sale->items as $index => $item)
                    <tr class="border-b border-slate-200 print-border">
                        <td class="py-3 px-3 text-center text-slate-500">{{ $index + 1 }}</td>
                        <td class="py-3 px-3">
                            <div class="font-bold text-gunmetal">{{ $item->productUnit->product->name }}</div>
                            <div class="text-xs text-slate-500">SKU: {{ $item->productUnit->product->sku }}</div>
                        </td>
                        <td class="py-3 px-3 text-center font-bold">
                            {{ $item->qty + 0 }} <span class="text-xs text-slate-500 font-normal">{{ $item->productUnit->unit->name }}</span>
                        </td>
                        <td class="py-3 px-3 text-right">Rp {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                        <td class="py-3 px-3 text-right font-bold text-gunmetal">Rp {{ number_format($item->subtotal(), 0, ',', '.') }}</td>
                    </tr>
                @endforeach
            </tbody>
        </table>

        <!-- TOTAL & TTD -->
        <div class="flex justify-between items-start mt-8">
            <div class="w-1/2">
                <div class="bg-teal-50 p-4 rounded border border-teal-100 print-border">
                    <p class="font-bold text-teal-800 text-sm mb-1">Catatan:</p>
                    <p class="text-teal-700 text-xs leading-relaxed">
                        Barang yang sudah dibeli tidak dapat ditukar atau dikembalikan kecuali ada perjanjian sebelumnya.<br>
                        Garansi berlaku sesuai dengan ketentuan pabrik (jika ada).
                    </p>
                </div>
            </div>
            
            <div class="w-5/12">
                <table class="w-full">
                    <tr class="border-b border-slate-200 print-border">
                        <td class="py-2 font-bold text-slate-600">Total Belanja</td>
                        <td class="py-2 text-right font-bold text-gunmetal text-lg">Rp {{ number_format($sale->total(), 0, ',', '.') }}</td>
                    </tr>
                    <tr class="border-b border-slate-200 print-border">
                        <td class="py-2 text-slate-600">Bayar ({{ $sale->payment_method }})</td>
                        <td class="py-2 text-right">Rp {{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
                    </tr>
                    <tr>
                        <td class="py-2 font-bold text-slate-600">Kembalian</td>
                        <td class="py-2 text-right font-bold text-slate-800">Rp {{ number_format($sale->change(), 0, ',', '.') }}</td>
                    </tr>
                </table>
                
                @if($sale->status === 'void')
                    <div class="mt-4 p-2 bg-red-100 text-red-800 text-center font-black uppercase tracking-widest border-2 border-red-500 rounded transform -rotate-2">
                        DIBATALKAN / VOID
                    </div>
                @else
                    <div class="mt-4 p-2 bg-emerald-100 text-emerald-800 text-center font-black uppercase tracking-widest border-2 border-emerald-500 rounded">
                        LUNAS / PAID
                    </div>
                @endif
            </div>
        </div>

        <div class="flex justify-between mt-16 pt-8 text-center text-sm">
            <div class="w-40">
                <p class="mb-16">Penerima / Pelanggan,</p>
                <div class="border-b border-slate-400 print-border"></div>
            </div>
            <div class="w-40">
                <p class="mb-16">Hormat Kami,</p>
                <div class="border-b border-slate-400 print-border"></div>
                <p class="mt-2 text-xs font-bold">{{ $sale->cashier->name }}</p>
            </div>
        </div>
    </div>

    <!-- Otomatis buka dialog print jika halaman dibuka -->
    <script>
        window.onload = function() {
            setTimeout(() => {
                // window.print(); // Di-comment untuk kenyamanan navigasi, user bisa klik tombol jika mau
            }, 500);
        }
    </script>
</body>
</html>
