<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-2xl text-gray-800 dark:text-gray-200 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8 space-y-6">
            
            @if(auth()->user()->isOwner())
            <!-- Financial Overview (Owner Only) -->
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
                <!-- Kas & Bank -->
                <div class="bg-white dark:bg-[#181A1B] overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-l-4 border-brand-primary">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Total Kas & Bank</h3>
                        <p class="text-3xl font-black text-brand-dark dark:text-white mt-2">Rp {{ number_format(($cash ?? 0) + ($bank ?? 0), 0, ',', '.') }}</p>
                        <div class="mt-2 text-xs text-gray-500">Kas: Rp {{ number_format($cash ?? 0, 0, ',', '.') }} | Bank: Rp {{ number_format($bank ?? 0, 0, ',', '.') }}</div>
                    </div>
                </div>

                <!-- Laba Hari Ini -->
                <div class="bg-white dark:bg-[#181A1B] overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-l-4 {{ ($today->net_income ?? 0) >= 0 ? 'border-brand-success' : 'border-brand-danger' }}">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Laba Bersih (Hari Ini)</h3>
                        <p class="text-3xl font-black {{ ($today->net_income ?? 0) >= 0 ? 'text-brand-success' : 'text-brand-danger' }} mt-2">
                            Rp {{ number_format($today->net_income ?? 0, 0, ',', '.') }}
                        </p>
                    </div>
                </div>

                <!-- Nilai Persediaan -->
                <div class="bg-white dark:bg-[#181A1B] overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-l-4 border-blue-500">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Nilai Persediaan</h3>
                        <p class="text-3xl font-black text-brand-dark dark:text-white mt-2">Rp {{ number_format($inventory ?? 0, 0, ',', '.') }}</p>
                        <div class="mt-2 text-xs text-gray-500">Estimasi aset barang dagang</div>
                    </div>
                </div>

                <!-- Utang Usaha -->
                <div class="bg-white dark:bg-[#181A1B] overflow-hidden shadow-sm rounded-lg border border-gray-200 dark:border-gray-700">
                    <div class="p-6 border-l-4 border-orange-500">
                        <h3 class="text-sm font-bold text-gray-500 uppercase tracking-wider">Utang Usaha</h3>
                        <p class="text-3xl font-black text-brand-dark dark:text-white mt-2">Rp {{ number_format($payable ?? 0, 0, ',', '.') }}</p>
                        <div class="mt-2 text-xs text-gray-500">Kewajiban ke supplier</div>
                    </div>
                </div>
            </div>
            
            <!-- Penjualan Terakhir & Grafik Placeholder -->
            <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
                <div class="lg:col-span-2 bg-white dark:bg-[#181A1B] shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Tren Penjualan (7 Hari)</h3>
                    <div class="h-64 flex items-end justify-between gap-2">
                        @if(isset($chart))
                            @foreach($chart as $day)
                                @php
                                    $max = collect($chart)->max('value') ?: 1;
                                    $height = ($day['value'] / $max) * 100;
                                @endphp
                                <div class="w-full flex flex-col items-center">
                                    <div class="w-full bg-brand-primary opacity-80 hover:opacity-100 rounded-t-md transition-all relative group" style="height: {{ max(5, $height) }}%;">
                                        <div class="absolute bottom-full mb-2 hidden group-hover:block bg-gray-800 text-white text-xs p-1 rounded whitespace-nowrap">
                                            Rp {{ number_format($day['value'], 0, ',', '.') }}
                                        </div>
                                    </div>
                                    <div class="text-[10px] text-gray-500 mt-2 truncate max-w-full" title="{{ $day['label'] }}">{{ explode(' ', $day['label'])[0] }}</div>
                                </div>
                            @endforeach
                        @endif
                    </div>
                </div>

                <div class="bg-white dark:bg-[#181A1B] shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <h3 class="text-lg font-bold mb-4 text-gray-800 dark:text-gray-200">Penjualan Terakhir</h3>
                    <div class="space-y-4">
                        @forelse($recentSales ?? [] as $sale)
                        <div class="flex justify-between items-center border-b border-gray-100 dark:border-gray-800 pb-2">
                            <div>
                                <div class="font-bold text-sm text-gray-800 dark:text-gray-200">{{ $sale->receipt_no }}</div>
                                <div class="text-xs text-gray-500">{{ $sale->created_at->format('H:i') }} - {{ $sale->cashier->name }}</div>
                            </div>
                            <div class="font-bold text-brand-success">Rp {{ number_format($sale->total_amount, 0, ',', '.') }}</div>
                        </div>
                        @empty
                        <div class="text-center text-sm text-gray-500 py-4">Belum ada transaksi hari ini</div>
                        @endforelse
                    </div>
                </div>
            </div>
            @endif

            <!-- Operational Overview (All Roles) -->
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                <!-- Peringatan Stok Menipis -->
                <div class="bg-white dark:bg-[#181A1B] shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center">
                            <span class="w-3 h-3 rounded-full bg-red-500 mr-2"></span> Stok Menipis
                        </h3>
                        <a href="{{ route('products.index') }}" class="text-sm text-brand-primary hover:underline">Lihat Semua</a>
                    </div>
                    <table class="w-full text-sm">
                        <tbody>
                            @forelse($lowStock as $product)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 text-gray-800 dark:text-gray-300">{{ $product->name }} <br><span class="text-xs text-gray-500">{{ $product->sku }}</span></td>
                                <td class="py-2 text-right font-bold text-red-600">{{ num($product->current_stock) }} {{ $product->baseUnit->name ?? 'Unit' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-4 text-center text-gray-500">Semua stok dalam batas aman.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>

                <!-- PO Menunggu / Parsial -->
                <div class="bg-white dark:bg-[#181A1B] shadow-sm rounded-lg border border-gray-200 dark:border-gray-700 p-6">
                    <div class="flex justify-between items-center mb-4">
                        <h3 class="text-lg font-bold text-gray-800 dark:text-gray-200 flex items-center">
                            <span class="w-3 h-3 rounded-full bg-orange-500 mr-2"></span> Purchase Order Aktif
                        </h3>
                        <a href="{{ route('purchase-orders.index') }}" class="text-sm text-brand-primary hover:underline">Lihat Semua</a>
                    </div>
                    <table class="w-full text-sm">
                        <tbody>
                            @forelse($openOrders as $po)
                            <tr class="border-b border-gray-100 dark:border-gray-800">
                                <td class="py-2 text-gray-800 dark:text-gray-300">{{ $po->po_number }} <br><span class="text-xs text-gray-500">{{ $po->supplier->name ?? 'Unknown' }}</span></td>
                                <td class="py-2 text-right">
                                    <span class="px-2 py-1 text-xs rounded {{ $po->status == 'partial' ? 'bg-orange-100 text-orange-800' : 'bg-blue-100 text-blue-800' }}">
                                        {{ strtoupper($po->status) }}
                                    </span>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="2" class="py-4 text-center text-gray-500">Tidak ada PO yang sedang aktif.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
