<!DOCTYPE html>
<html lang="id">
<head>
    <meta charset="utf-8">
    <title>Struk #{{ $sale->invoice_number }}</title>
    <style>
        /* Desain spesifik untuk printer thermal 58mm atau 80mm */
        @media print {
            body { font-size: 12px; color: black; background: white; margin: 0; padding: 0; }
            .no-print { display: none !important; }
        }
        body { 
            font-family: monospace; 
            background: #f1f5f9; 
            color: #000; 
            padding: 10px;
        }
        .receipt-box {
            max-width: 300px; /* Lebar aman untuk thermal 80mm, akan menyesuaikan 58mm */
            margin: 0 auto;
            background: white;
            padding: 15px;
            box-shadow: 0 2px 4px rgba(0,0,0,0.1);
        }
        @media print {
            .receipt-box { width: 100%; max-width: 100%; box-shadow: none; margin: 0; padding: 0; }
        }
        .text-center { text-align: center; }
        .text-right { text-align: right; }
        .font-bold { font-weight: bold; }
        .mb-1 { margin-bottom: 5px; }
        .mb-2 { margin-bottom: 10px; }
        .mt-2 { margin-top: 10px; }
        .border-dashed { border-bottom: 1px dashed #000; margin: 10px 0; }
        table { width: 100%; border-collapse: collapse; }
        td { vertical-align: top; padding: 2px 0; }
        
        /* Tombol print untuk tes di layar */
        .print-btn {
            display: block;
            width: 100%;
            padding: 10px;
            background: #0d9488;
            color: white;
            text-align: center;
            border: none;
            border-radius: 5px;
            cursor: pointer;
            font-family: sans-serif;
            font-weight: bold;
            margin-bottom: 15px;
        }
    </style>
</head>
<body>

    <button onclick="window.print()" class="no-print print-btn">🖨️ Cetak Struk (Thermal)</button>

    <div class="receipt-box">
        <div class="text-center font-bold mb-1" style="font-size: 16px;">Toko Berkat Jaya</div>
        <div class="text-center mb-1">Jl. Raya Elektronik No. 123</div>
        <div class="text-center mb-2">Telp: (021) 555-0198</div>
        <div class="border-dashed"></div>
        
        <table>
            <tr>
                <td>Tgl</td>
                <td>: {{ \Carbon\Carbon::parse($sale->sale_date)->format('d/m/y H:i') }}</td>
            </tr>
            <tr>
                <td>Kasir</td>
                <td>: {{ mb_substr($sale->cashier->name, 0, 15) }}</td>
            </tr>
            <tr>
                <td>Nota</td>
                <td>: {{ $sale->invoice_number }}</td>
            </tr>
        </table>
        
        <div class="border-dashed"></div>
        
        <table>
            @foreach($sale->items as $item)
            <tr>
                <td colspan="3" class="font-bold">{{ mb_substr($item->productUnit->product->name, 0, 30) }}</td>
            </tr>
            <tr>
                <td>{{ $item->qty + 0 }} {{ $item->productUnit->unit->name }}</td>
                <td class="text-right">x {{ number_format($item->selling_price, 0, ',', '.') }}</td>
                <td class="text-right">{{ number_format($item->subtotal(), 0, ',', '.') }}</td>
            </tr>
            @endforeach
        </table>
        
        <div class="border-dashed"></div>
        
        <table>
            <tr>
                <td class="font-bold">Total</td>
                <td class="text-right font-bold" style="font-size: 14px;">{{ number_format($sale->total(), 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Bayar ({{ $sale->payment_method }})</td>
                <td class="text-right">{{ number_format($sale->amount_paid, 0, ',', '.') }}</td>
            </tr>
            <tr>
                <td>Kembali</td>
                <td class="text-right">{{ number_format($sale->change(), 0, ',', '.') }}</td>
            </tr>
        </table>
        
        <div class="border-dashed"></div>
        
        <div class="text-center mb-1">Terima Kasih</div>
        <div class="text-center">Barang yang sudah dibeli<br>tidak dapat ditukar</div>
        
        <div style="height: 30px;"></div> <!-- Spasi kosong agar printer thermal bisa motong kertas dengan pas -->
    </div>

    <!-- Otomatis buka dialog print jika halaman dibuka -->
    <script>
        window.onload = function() {
            setTimeout(() => {
                window.print();
            }, 500);
        }
    </script>
</body>
</html>
