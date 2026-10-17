<x-app-layout>
    @php
        $isOwner = auth()->user()->isOwner();
        $payable = $po->payableAmount();
        $paid = $po->paidAmount();
        $outstanding = $po->outstandingAmount();
        $pStatus = $po->paymentStatus();
        $canReturn = $po->items->contains(fn ($i) => $i->qtyReturnable() > 0);
        $statusBadge = ['completed' => 'badge-green', 'cancelled' => 'badge-red', 'partial' => 'badge-orange', 'open' => 'badge-blue'][$po->status] ?? 'badge-gray';
        $payBadge = ['paid' => 'badge-green', 'partial' => 'badge-orange', 'unpaid' => 'badge-red', 'none' => 'badge-gray'][$pStatus];
    @endphp

    <x-slot name="header">
        <div class="flex w-full flex-wrap items-center justify-between gap-3">
            <div>
                <h2 class="page-title">PO <span class="font-mono text-teal-700">{{ $po->po_number }}</span></h2>
                <p class="page-subtitle">{{ $po->supplier->name }} · dipesan {{ $po->order_date->format('d/m/Y') }} oleh {{ $po->creator->name ?? '-' }}</p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Kembali</a>
        </div>
    </x-slot>

    <div class="mb-6 grid gap-4 sm:grid-cols-2 lg:grid-cols-4">
        <div class="kpi">
            <div class="kpi-label">Status Pesanan</div>
            <div class="mt-2"><span class="badge {{ $statusBadge }}">{{ $po->statusLabel() }}</span></div>
            <div class="kpi-foot">Nilai pesanan {{ rp($po->orderedAmount()) }}</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Utang Diakui</div>
            <div class="kpi-value">{{ rp($payable) }}</div>
            <div class="kpi-foot">barang diterima − retur</div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Sudah Dibayar</div>
            <div class="kpi-value text-emerald-700">{{ rp($paid) }}</div>
            <div class="kpi-foot"><span class="badge {{ $payBadge }}">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$pStatus] }}</span></div>
        </div>
        <div class="kpi">
            <div class="kpi-label">Sisa Utang</div>
            <div class="kpi-value {{ $outstanding > 0 ? 'text-orange-600' : '' }}">{{ rp($outstanding) }}</div>
            <div class="kpi-foot">belum dilunasi</div>
        </div>
    </div>

    {{-- Aksi --}}
    <div class="mb-6 flex flex-wrap gap-3">
        @if ($po->canReceive())
            <a href="{{ route('purchase-orders.receive.form', $po) }}" class="btn btn-primary" id="btn-receive">Terima Barang</a>
        @endif
        @if ($canReturn)
            <a href="{{ route('purchase-orders.return.form', $po) }}" class="btn btn-warning" id="btn-return">Retur ke Supplier</a>
        @endif
        @if ($po->status === 'partial')
            <form method="POST" action="{{ route('purchase-orders.complete', $po) }}" onsubmit="return confirm('Tutup PO? Sisa barang yang belum datang tidak akan diterima lagi.');">
                @csrf
                <button type="submit" class="btn btn-secondary">Tutup PO (sisa tidak datang)</button>
            </form>
        @endif
        @if ($po->canCancel())
            <form method="POST" action="{{ route('purchase-orders.cancel', $po) }}" onsubmit="return confirm('Batalkan PO ini?');">
                @csrf
                <button type="submit" class="btn btn-danger">Batalkan PO</button>
            </form>
        @endif
    </div>

    <div class="grid gap-6 lg:grid-cols-3">
        <div class="space-y-6 lg:col-span-2">
            {{-- Item --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Rincian Barang</h3></div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Produk</th>
                                <th class="text-right">Harga</th>
                                <th class="text-right">Dipesan</th>
                                <th class="text-right">Diterima</th>
                                <th class="text-right">Ditolak</th>
                                <th class="text-right">Diretur</th>
                                <th class="text-right">Sisa</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach ($po->items as $item)
                                @php $unitName = $item->productUnit->unit->name; @endphp
                                <tr>
                                    <td>
                                        <div class="font-bold">{{ $item->productUnit->product->name }}</div>
                                        <div class="text-xs text-slate-500">{{ $item->productUnit->product->sku }} · {{ $unitName }}</div>
                                    </td>
                                    <td class="num">{{ rp($item->unit_price) }}</td>
                                    <td class="num">{{ num($item->qty_ordered) }}</td>
                                    <td class="num text-emerald-700">{{ num($item->qtyAccepted()) }}</td>
                                    <td class="num text-red-600">{{ num($item->qtyRejected()) }}</td>
                                    <td class="num text-orange-600">{{ num($item->qtyReturned()) }}</td>
                                    <td class="num font-bold">{{ num($item->qtyRemaining()) }}</td>
                                </tr>
                            @endforeach
                        </tbody>
                        <tfoot>
                            <tr>
                                <td colspan="6" class="text-right text-xs uppercase tracking-wider text-slate-500">Nilai Pesanan</td>
                                <td class="num">{{ rp($po->orderedAmount()) }}</td>
                            </tr>
                        </tfoot>
                    </table>
                </div>
            </div>

            {{-- Riwayat penerimaan --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Riwayat Penerimaan</h3></div>
                <div class="card-body space-y-4">
                    @forelse ($po->receipts as $gr)
                        <div class="rounded-lg border border-slate-200 p-4">
                            <div class="flex flex-wrap items-center justify-between gap-2">
                                <div class="font-mono text-sm font-bold text-teal-700">{{ $gr->gr_number }}</div>
                                <div class="text-xs text-slate-500">{{ $gr->receipt_date->format('d/m/Y') }} · {{ $gr->receiver->name ?? '-' }}</div>
                            </div>
                            <ul class="mt-2 space-y-1 text-sm">
                                @foreach ($gr->items as $gi)
                                    <li class="flex justify-between gap-3">
                                        <span>{{ $gi->purchaseOrderItem->productUnit->product->name }}</span>
                                        <span class="num">+{{ num($gi->qtyAccepted()) }} @if ((float) $gi->qty_rejected > 0)<span class="text-red-600">(tolak {{ num($gi->qty_rejected) }})</span>@endif</span>
                                    </li>
                                @endforeach
                            </ul>
                            @if ($gr->notes) <p class="mt-2 text-xs italic text-slate-500">“{{ $gr->notes }}”</p> @endif
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada barang yang diterima.</p>
                    @endforelse
                </div>
            </div>

            {{-- Riwayat retur --}}
            @if ($po->returns->isNotEmpty())
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Riwayat Retur</h3></div>
                    <div class="card-body space-y-4">
                        @foreach ($po->returns as $ret)
                            <div class="rounded-lg border border-orange-200 bg-orange-50/50 p-4">
                                <div class="flex flex-wrap items-center justify-between gap-2">
                                    <div class="font-mono text-sm font-bold text-orange-700">{{ $ret->return_number }}</div>
                                    <div class="text-xs text-slate-500">{{ $ret->return_date->format('d/m/Y') }}</div>
                                </div>
                                <ul class="mt-2 space-y-1 text-sm">
                                    @foreach ($ret->items as $ri)
                                        <li class="flex justify-between gap-3">
                                            <span>{{ $ri->purchaseOrderItem->productUnit->product->name }}</span>
                                            <span class="num">−{{ num($ri->qty) }}</span>
                                        </li>
                                    @endforeach
                                </ul>
                                <p class="mt-2 text-xs italic text-slate-600">Alasan: {{ $ret->reason }}</p>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>

        {{-- Pembayaran --}}
        <div class="space-y-6">
            @if ($isOwner && $outstanding > 0)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Bayar Utang Supplier</h3></div>
                    <div class="card-body">
                        <form method="POST" action="{{ route('purchase-orders.pay', $po) }}" class="space-y-4">
                            @csrf
                            <div>
                                <label class="label" for="payment_date">Tanggal</label>
                                <input id="payment_date" type="date" name="payment_date" max="{{ today()->toDateString() }}" value="{{ old('payment_date', today()->toDateString()) }}" class="input" required>
                            </div>
                            <div>
                                <label class="label" for="amount">Jumlah (Rp)</label>
                                <input id="amount" type="number" step="0.01" min="0.01" max="{{ $outstanding }}" name="amount" value="{{ old('amount', $outstanding) }}" class="input" required>
                                <p class="hint">Sisa utang {{ rp($outstanding) }}. Boleh dicicil.</p>
                            </div>
                            <div>
                                <label class="label" for="account_id">Dibayar dari</label>
                                <select id="account_id" name="account_id" class="input" required>
                                    @foreach ($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" @selected(old('account_id') == $acc->id)>{{ $acc->code }} — {{ $acc->name }}</option>
                                    @endforeach
                                </select>
                            </div>
                            <div>
                                <label class="label" for="pay_notes">Catatan</label>
                                <input id="pay_notes" type="text" name="notes" maxlength="255" value="{{ old('notes') }}" class="input" placeholder="Opsional">
                            </div>
                            <button type="submit" class="btn btn-primary w-full" id="btn-pay">Catat Pembayaran</button>
                        </form>
                    </div>
                </div>
            @endif

            <div class="card">
                <div class="card-header"><h3 class="card-title">Riwayat Pembayaran</h3></div>
                <div class="card-body space-y-3">
                    @forelse ($po->payments as $pay)
                        <div class="flex items-start justify-between gap-3 border-b border-slate-100 pb-3 last:border-0 last:pb-0">
                            <div>
                                <div class="font-mono text-xs font-bold text-teal-700">{{ $pay->payment_number }}</div>
                                <div class="text-xs text-slate-500">{{ $pay->payment_date->format('d/m/Y') }} · {{ $pay->account->name ?? '-' }}</div>
                                @if ($pay->notes) <div class="text-xs italic text-slate-500">“{{ $pay->notes }}”</div> @endif
                            </div>
                            <div class="num font-bold text-emerald-700">{{ rp($pay->amount) }}</div>
                        </div>
                    @empty
                        <p class="text-sm text-slate-500">Belum ada pembayaran.</p>
                    @endforelse
                </div>
            </div>

            @if ($po->notes)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Catatan PO</h3></div>
                    <div class="card-body text-sm text-slate-600">{{ $po->notes }}</div>
                </div>
            @endif
        </div>
    </div>
</x-app-layout>
