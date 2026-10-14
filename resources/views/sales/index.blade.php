<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Riwayat Penjualan</h2>
        <p class="page-subtitle">Daftar semua transaksi penjualan dari POS Kasir.</p>
    </x-slot>

    <div class="mb-6 grid grid-cols-1 md:grid-cols-2 gap-4">
        <div class="kpi">
            <div class="kpi-label">Total Transaksi Selesai</div>
            <div class="kpi-value text-teal-600">{{ number_format($summaryCount, 0, ',', '.') }}</div>
            <div class="kpi-foot">Periode: {{ $from->format('d/m/Y') }} - {{ $to->format('d/m/Y') }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Total Pendapatan (Gross)</div>
            <div class="kpi-value text-emerald-600">Rp {{ number_format($summaryTotal, 0, ',', '.') }}</div>
            <div class="kpi-foot">Dari transaksi status "Completed"</div>
        </div>
    </div>

    <div class="card mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('sales.index') }}" class="flex flex-wrap gap-3 items-end w-full">
                <div>
                    <label class="label">Mulai Tanggal</label>
                    <input type="date" name="from" value="{{ $from->format('Y-m-d') }}" class="input w-40">
                </div>
                <div>
                    <label class="label">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to->format('Y-m-d') }}" class="input w-40">
                </div>
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input w-36">
                        <option value="">Semua</option>
                        <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Selesai</option>
                        <option value="voided" {{ request('status') === 'voided' ? 'selected' : '' }}>Dibatalkan</option>
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label">Cari Invoice</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: INV-2026..." class="input">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Filter</button>
                    <a href="{{ route('sales.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal & Waktu</th>
                        <th>No. Struk (Invoice)</th>
                        <th>Kasir</th>
                        <th>Metode Bayar</th>
                        <th class="text-right">Total Transaksi</th>
                        <th class="text-center">Status</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($sales as $sale)
                    <tr>
                        <td>
                            <div class="font-semibold">{{ $sale->sale_date->format('d/m/Y') }}</div>
                            <div class="text-xs text-slate-500">{{ $sale->created_at->format('H:i') }}</div>
                        </td>
                        <td class="font-mono font-medium">{{ $sale->receipt_no ?? $sale->invoice_number }}</td>
                        <td>{{ $sale->cashier->name }}</td>
                        <td>{{ $sale->payment_method }}</td>
                        <td class="num text-teal-700 font-bold">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</td>
                        <td class="text-center">
                            @if($sale->status === 'completed')
                                <span class="badge badge-green">Selesai</span>
                            @elseif($sale->status === 'voided')
                                <span class="badge badge-red">Void</span>
                            @else
                                <span class="badge badge-gray">{{ $sale->status }}</span>
                            @endif
                        </td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('sales.show', $sale) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-800">Detail</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="7" class="py-8 text-center text-slate-500">
                            Tidak ada transaksi penjualan yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($sales->hasPages())
        <div class="border-t border-slate-200 p-4">
            {{ $sales->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
