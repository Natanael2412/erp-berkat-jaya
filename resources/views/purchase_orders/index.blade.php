<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="page-title">Purchase Orders (PO)</h2>
                <p class="page-subtitle">Kelola pesanan pembelian barang ke pemasok (supplier).</p>
            </div>
            <a href="{{ route('purchase-orders.create') }}" class="btn btn-primary">
                <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"></path></svg>
                Buat PO Baru
            </a>
        </div>
    </x-slot>

    <div class="card mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('purchase-orders.index') }}" class="flex flex-wrap gap-3 items-end w-full">
                <div>
                    <label class="label">Status</label>
                    <select name="status" class="input w-40">
                        <option value="">Semua Status</option>
                        @foreach(\App\Models\PurchaseOrder::STATUSES as $val => $label)
                            <option value="{{ $val }}" {{ request('status') === $val ? 'selected' : '' }}>{{ $label }}</option>
                        @endforeach
                    </select>
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label">Cari PO atau Supplier</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: PO-2026... atau Nama PT" class="input">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Filter</button>
                    <a href="{{ route('purchase-orders.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal Order</th>
                        <th>No. PO</th>
                        <th>Supplier</th>
                        <th class="text-right">Nilai Pemesanan</th>
                        <th class="text-right">Utang Diakui</th>
                        <th class="text-center">Status Pemesanan</th>
                        <th class="text-center">Status Bayar</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($orders as $order)
                    <tr>
                        <td>{{ $order->order_date->format('d/m/Y') }}</td>
                        <td class="font-mono font-medium text-teal-700">{{ $order->po_number }}</td>
                        <td class="font-bold">{{ $order->supplier->name }}</td>
                        <td class="num">Rp {{ number_format($order->orderedAmount(), 0, ',', '.') }}</td>
                        <td class="num {{ $order->payableAmount() > 0 ? 'text-orange-600 font-bold' : '' }}">
                            Rp {{ number_format($order->payableAmount(), 0, ',', '.') }}
                        </td>
                        <td class="text-center">
                            @if($order->status === 'completed')
                                <span class="badge badge-green">{{ $order->statusLabel() }}</span>
                            @elseif($order->status === 'cancelled')
                                <span class="badge badge-red">{{ $order->statusLabel() }}</span>
                            @elseif($order->status === 'partial')
                                <span class="badge badge-orange">{{ $order->statusLabel() }}</span>
                            @else
                                <span class="badge badge-blue">{{ $order->statusLabel() }}</span>
                            @endif
                        </td>
                        <td class="text-center">
                            @php $pStatus = $order->paymentStatus(); @endphp
                            @if($pStatus === 'paid')
                                <span class="badge badge-green">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$pStatus] }}</span>
                            @elseif($pStatus === 'partial')
                                <span class="badge badge-orange">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$pStatus] }}</span>
                            @elseif($pStatus === 'unpaid')
                                <span class="badge badge-red">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$pStatus] }}</span>
                            @else
                                <span class="badge badge-gray">{{ \App\Models\PurchaseOrder::PAYMENT_STATUSES[$pStatus] }}</span>
                            @endif
                        </td>
                        <td class="text-right space-x-2">
                            <a href="{{ route('purchase-orders.show', $order) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-800">Detail & Penerimaan</a>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="8" class="py-8 text-center text-slate-500">
                            Tidak ada pesanan pembelian yang ditemukan.
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($orders->hasPages())
        <div class="border-t border-slate-200 p-4">
            {{ $orders->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
