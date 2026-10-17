<x-app-layout>
    @php
        $isOwner = auth()->user()->isOwner();
        $chartMax = $isOwner ? max(1, collect($chart)->max('value')) : 1;
    @endphp

    <x-slot name="header">
        <div>
            <h2 class="page-title">Dashboard</h2>
            <p class="page-subtitle">{{ now()->translatedFormat('l, d F Y') }} · Selamat bekerja, {{ auth()->user()->name }}.</p>
        </div>
    </x-slot>

    @if ($isOwner)
        <div class="mb-6 grid gap-4 sm:grid-cols-2 xl:grid-cols-4">
            <div class="kpi border-l-4 border-l-teal-600">
                <div class="kpi-label">Kas &amp; Bank</div>
                <div class="kpi-value">{{ rp($cash + $bank) }}</div>
                <div class="kpi-foot">Kas {{ rp($cash) }} · Bank {{ rp($bank) }}</div>
            </div>
            <div class="kpi border-l-4 {{ $today['net_income'] >= 0 ? 'border-l-emerald-500' : 'border-l-red-500' }}">
                <div class="kpi-label">Laba Bersih Hari Ini</div>
                <div class="kpi-value {{ $today['net_income'] >= 0 ? 'text-emerald-600' : 'text-red-600' }}">{{ rp($today['net_income']) }}</div>
                <div class="kpi-foot">Penjualan bersih {{ rp($today['net_sales']) }}</div>
            </div>
            <div class="kpi border-l-4 border-l-orange-500">
                <div class="kpi-label">Laba Bersih Bulan Ini</div>
                <div class="kpi-value {{ $month['net_income'] >= 0 ? '' : 'text-red-600' }}">{{ rp($month['net_income']) }}</div>
                <div class="kpi-foot">Laba kotor {{ rp($month['gross_profit']) }}</div>
            </div>
            <div class="kpi border-l-4 border-l-slate-800">
                <div class="kpi-label">Persediaan · Utang Usaha</div>
                <div class="kpi-value">{{ rp($inventory) }}</div>
                <div class="kpi-foot">Utang ke supplier {{ rp($payable) }}</div>
            </div>
        </div>

        @if (abs($stockValueCheck - $inventory) >= 1)
            <div class="mb-6 rounded-lg border border-orange-300 bg-orange-50 px-4 py-3 text-sm text-orange-900" role="alert">
                <strong>Selisih persediaan terdeteksi.</strong>
                Nilai kartu stok {{ rp($stockValueCheck) }} berbeda dengan saldo akun Persediaan {{ rp($inventory) }}
                (selisih {{ rp(abs($stockValueCheck - $inventory)) }}). Periksa Jurnal Umum &amp; kartu stok.
            </div>
        @endif

        <div class="mb-6 grid gap-6 lg:grid-cols-3">
            <div class="card lg:col-span-2">
                <div class="card-header"><h3 class="card-title">Penjualan 7 Hari Terakhir</h3></div>
                <div class="card-body">
                    <div class="flex h-56 items-end gap-3">
                        @foreach ($chart as $day)
                            @php $h = $day['value'] > 0 ? max(4, round($day['value'] / $chartMax * 100)) : 2; @endphp
                            <div class="group flex h-full flex-1 flex-col items-center justify-end">
                                <div class="mb-1 hidden whitespace-nowrap rounded bg-gunmetal px-2 py-1 text-[11px] font-semibold text-white group-hover:block">{{ rp($day['value']) }}</div>
                                <div class="w-full rounded-t-md {{ $loop->last ? 'bg-orange-500' : 'bg-teal-600/80' }} transition group-hover:opacity-100" style="height: {{ $h }}%"></div>
                                <div class="mt-2 text-[11px] font-medium text-slate-500">{{ $day['label'] }}</div>
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Penjualan Terakhir</h3>
                    <a href="{{ route('sales.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Semua</a>
                </div>
                <div class="card-body space-y-3">
                    @forelse ($recentSales as $sale)
                        <a href="{{ route('sales.show', $sale) }}" class="flex items-center justify-between gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0 hover:text-teal-700">
                            <div class="min-w-0">
                                <div class="truncate font-mono text-xs font-bold {{ $sale->isVoid() ? 'text-red-500 line-through' : '' }}">{{ $sale->invoice_number }}</div>
                                <div class="text-xs text-slate-500">{{ $sale->sale_date->format('d/m H:i') }} · {{ $sale->cashier->name ?? '-' }}</div>
                            </div>
                            <div class="num font-bold">{{ rp($sale->total()) }}</div>
                        </a>
                    @empty
                        <p class="py-4 text-center text-sm text-slate-500">Belum ada transaksi.</p>
                    @endforelse
                </div>
            </div>
        </div>
    @endif

    <div class="grid gap-6 md:grid-cols-2">
        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-red-500"></span> Stok Menipis</h3>
                <a href="{{ route('products.index', ['filter' => 'low']) }}" class="text-xs font-semibold text-teal-600 hover:underline">Lihat semua</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        @forelse ($lowStock as $p)
                            <tr>
                                <td>
                                    <a href="{{ route('products.show', $p) }}" class="font-semibold hover:text-teal-700">{{ $p->name }}</a>
                                    <div class="font-mono text-xs text-slate-500">{{ $p->sku }}</div>
                                </td>
                                <td class="num font-bold text-red-600">{{ num($p->current_stock) }} {{ $p->baseUnit->name }}
                                    <div class="text-xs font-normal text-slate-500">min {{ num($p->min_stock) }}</div>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-slate-500">Semua stok dalam batas aman. 👍</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>

        <div class="card">
            <div class="card-header">
                <h3 class="card-title flex items-center gap-2"><span class="h-2.5 w-2.5 rounded-full bg-orange-500"></span> PO Menunggu Barang</h3>
                <a href="{{ route('purchase-orders.index') }}" class="text-xs font-semibold text-teal-600 hover:underline">Lihat semua</a>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <tbody>
                        @forelse ($openOrders as $po)
                            <tr>
                                <td>
                                    <a href="{{ route('purchase-orders.show', $po) }}" class="font-mono text-sm font-bold text-teal-700 hover:underline">{{ $po->po_number }}</a>
                                    <div class="text-xs text-slate-500">{{ $po->supplier->name ?? '-' }}</div>
                                </td>
                                <td class="text-right">
                                    <span class="badge {{ $po->status === 'partial' ? 'badge-orange' : 'badge-blue' }}">{{ $po->statusLabel() }}</span>
                                </td>
                            </tr>
                        @empty
                            <tr><td class="py-6 text-center text-slate-500">Tidak ada PO yang sedang berjalan.</td></tr>
                        @endforelse
                    </tbody>
                </table>
            </div>
        </div>
    </div>
</x-app-layout>
