<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">{{ $product->name }}</h2>
                <p class="page-subtitle"><span class="font-mono">{{ $product->sku }}</span> · Kartu stok &amp; HPP rata-rata bergerak</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('products.edit', $product) }}" class="btn btn-dark">Edit Produk</a>
                <a href="{{ route('products.index') }}" class="btn btn-secondary">Kembali</a>
            </div>
        </div>
    </x-slot>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="kpi">
            <div class="kpi-label">Stok Saat Ini</div>
            <div class="kpi-value {{ $product->isLowStock() ? 'text-red-600' : '' }}">{{ num($product->current_stock) }}</div>
            <div class="kpi-foot">{{ $product->baseUnit->name }} @if ($product->isLowStock()) · <span class="font-bold text-red-600">menipis</span> @endif</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">HPP Rata-rata</div>
            <div class="kpi-value">{{ rp($product->current_hpp) }}</div>
            <div class="kpi-foot">per {{ $product->baseUnit->name }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Nilai Persediaan</div>
            <div class="kpi-value">{{ rp($product->stockValue()) }}</div>
            <div class="kpi-foot">stok × HPP</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Stok Minimum</div>
            <div class="kpi-value">{{ num($product->min_stock) }}</div>
            <div class="kpi-foot">{{ $product->is_active ? 'Produk aktif' : 'Produk nonaktif' }}</div>
        </div>
    </div>

    <div class="mb-6 card">
        <div class="card-header"><h3 class="card-title">Satuan &amp; Harga Jual</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr><th>Satuan</th><th class="text-right">Isi (satuan dasar)</th><th class="text-right">Harga Jual</th><th class="text-center">Status</th></tr>
                </thead>
                <tbody>
                    @foreach ($product->units as $pu)
                        <tr>
                            <td class="font-semibold">
                                {{ $pu->unit->name }}
                                @if ($pu->unit_id === $product->base_unit_id) <span class="badge badge-teal ml-1">Dasar</span> @endif
                            </td>
                            <td class="num">{{ num($pu->conversion_factor, 0) }} {{ $product->baseUnit->name }}</td>
                            <td class="num">{{ rp($pu->selling_price) }}</td>
                            <td class="text-center">
                                <span class="badge {{ $pu->is_active ? 'badge-green' : 'badge-gray' }}">{{ $pu->is_active ? 'Aktif' : 'Nonaktif' }}</span>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>

    @if ($product->batches->isNotEmpty())
        <div class="mb-6 card">
            <div class="card-header">
                <h3 class="card-title">Daftar Entitas Stok Rol Fisik (Batch / SKU Terpisah)</h3>
                <span class="badge badge-teal">{{ $product->batches->where('remaining_qty', '>', 0)->count() }} Rol Aktif</span>
            </div>
            <div class="table-wrap">
                <table class="table">
                    <thead>
                        <tr>
                            <th>SKU Batch Rol</th>
                            <th>Ref. Penerimaan</th>
                            <th>Tanggal Masuk</th>
                            <th class="text-right">Kapasitas Awal</th>
                            <th class="text-right">Sisa Stok Rol</th>
                            <th class="text-right">HPP Masuk / {{ $product->baseUnit->name }}</th>
                            <th class="text-center">Status Rol</th>
                        </tr>
                    </thead>
                    <tbody>
                        @foreach ($product->batches as $batch)
                            <tr>
                                <td class="font-mono text-xs font-bold text-teal-700">{{ $batch->batch_sku }}</td>
                                <td class="font-mono text-xs">
                                    @if ($batch->goodsReceipt)
                                        <a href="{{ route('purchase-orders.show', $batch->goodsReceipt->purchase_order_id) }}" class="text-teal-600 hover:underline">
                                            {{ $batch->goodsReceipt->gr_number }}
                                        </a>
                                    @else
                                        Saldo Awal
                                    @endif
                                </td>
                                <td>{{ $batch->received_date ? $batch->received_date->format('d/m/Y') : '—' }}</td>
                                <td class="num">{{ num($batch->initial_qty, 0) }} {{ $product->baseUnit->name }}</td>
                                <td class="num font-bold {{ $batch->remaining_qty > 0 ? 'text-emerald-700' : 'text-slate-400' }}">
                                    {{ num($batch->remaining_qty, 0) }} {{ $product->baseUnit->name }}
                                </td>
                                <td class="num">{{ rp($batch->unit_cost) }}</td>
                                <td class="text-center">
                                    @if ($batch->remaining_qty <= 0)
                                        <span class="badge badge-gray">Habis</span>
                                    @elseif ($batch->remaining_qty < $batch->initial_qty)
                                        <span class="badge badge-orange">Terpotong (Sisa {{ $batch->remaining_qty }} {{ $product->baseUnit->name }})</span>
                                    @else
                                        <span class="badge badge-green">Utuh ({{ $batch->initial_qty }} {{ $product->baseUnit->name }})</span>
                                    @endif
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    <div class="card">
        <div class="card-header"><h3 class="card-title">Kartu Stok</h3></div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jenis</th>
                        <th>Referensi / Batch</th>
                        <th class="text-right">Masuk</th>
                        <th class="text-right">Keluar</th>
                        <th class="text-right">Biaya/Unit</th>
                        <th class="text-right">Saldo</th>
                        <th class="text-right">HPP Saldo</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($movements as $m)
                        @php
                            $ref = $m->source->gr_number ?? $m->source->invoice_number ?? $m->source->return_number ?? '—';
                            $in = in_array($m->type, ['opening', 'purchase', 'sale_void'], true);
                        @endphp
                        <tr>
                            <td class="whitespace-nowrap">{{ $m->movement_date->format('d/m/Y H:i') }}</td>
                            <td><span class="badge {{ $in ? 'badge-green' : 'badge-orange' }}">{{ $m->typeLabel() }}</span></td>
                            <td class="font-mono text-xs">
                                {{ $ref }}
                                @if ($m->batch)
                                    <span class="ml-1 rounded bg-slate-100 px-1.5 py-0.5 text-[10px] font-semibold text-teal-700">{{ $m->batch->batch_sku }}</span>
                                @endif
                            </td>
                            <td class="num text-emerald-700">{{ (float) $m->qty_in > 0 ? '+'.num($m->qty_in, 0) : '—' }}</td>
                            <td class="num text-orange-700">{{ (float) $m->qty_out > 0 ? '−'.num($m->qty_out, 0) : '—' }}</td>
                            <td class="num">{{ rp($m->unit_cost) }}</td>
                            <td class="num font-bold">{{ num($m->balance_qty, 0) }}</td>
                            <td class="num text-slate-500">{{ rp($m->balance_hpp) }}</td>
                        </tr>
                    @empty
                        <tr><td colspan="8" class="py-10 text-center text-slate-500">Belum ada mutasi stok untuk produk ini.</td></tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if ($movements->hasPages())
            <div class="border-t border-slate-200 p-4">{{ $movements->links() }}</div>
        @endif
    </div>
</x-app-layout>
