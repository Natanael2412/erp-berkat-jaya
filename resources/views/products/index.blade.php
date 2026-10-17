<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Produk &amp; Stok</h2>
                <p class="page-subtitle">Master barang, stok terkini, dan HPP rata-rata bergerak.</p>
            </div>
            <a href="{{ route('products.create') }}" class="btn btn-primary" id="btn-add-product">
                <svg class="h-4 w-4" fill="none" stroke="currentColor" stroke-width="2.5" viewBox="0 0 24 24"><path stroke-linecap="round" d="M12 5v14M5 12h14"/></svg>
                Tambah Produk
            </a>
        </div>
    </x-slot>

    <div class="card">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('products.index') }}" class="flex w-full flex-wrap items-end gap-3">
                <div class="min-w-[220px] flex-1">
                    <label class="label" for="q">Cari SKU / Nama</label>
                    <input id="q" type="text" name="q" value="{{ request('q') }}" class="input" placeholder="Contoh: kabel, LED, KBL-...">
                </div>
                <div>
                    <label class="label" for="filter">Tampilkan</label>
                    <select id="filter" name="filter" class="input w-48">
                        <option value="">Produk aktif</option>
                        <option value="low" @selected(request('filter') === 'low')>Stok menipis</option>
                        <option value="inactive" @selected(request('filter') === 'inactive')>Nonaktif</option>
                    </select>
                </div>
                <div class="flex gap-2">
                    <button type="submit" class="btn btn-dark">Filter</button>
                    <a href="{{ route('products.index') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>SKU</th>
                        <th>Nama Produk</th>
                        <th>Satuan &amp; Harga Jual</th>
                        <th class="text-right">Stok</th>
                        <th class="text-right">Min.</th>
                        <th class="text-right">HPP</th>
                        <th class="text-right">Nilai Persediaan</th>
                        <th class="text-right">Aksi</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse ($products as $product)
                        <tr>
                            <td class="font-mono text-xs font-semibold text-teal-700">{{ $product->sku }}</td>
                            <td>
                                <a href="{{ route('products.show', $product) }}" class="font-bold text-gunmetal hover:text-teal-700">{{ $product->name }}</a>
                                @unless ($product->is_active)
                                    <span class="badge badge-gray ml-1">Nonaktif</span>
                                @endunless
                            </td>
                            <td>
                                <div class="flex flex-wrap gap-1">
                                    @foreach ($product->units->where('is_active', true) as $pu)
                                        <span class="badge badge-gray" title="1 {{ $pu->unit->name }} = {{ num($pu->conversion_factor) }} {{ $product->baseUnit->name }}">
                                            {{ $pu->unit->name }} · {{ rp($pu->selling_price) }}
                                        </span>
                                    @endforeach
                                </div>
                            </td>
                            <td class="num font-bold {{ $product->isLowStock() ? 'text-red-600' : '' }}">
                                {{ num($product->current_stock) }} <span class="text-xs font-medium text-slate-500">{{ $product->baseUnit->name }}</span>
                                @if ($product->isLowStock())
                                    <span class="badge badge-red ml-1">Menipis</span>
                                @endif
                            </td>
                            <td class="num text-slate-500">{{ num($product->min_stock) }}</td>
                            <td class="num">{{ rp($product->current_hpp) }}</td>
                            <td class="num font-semibold">{{ rp($product->stockValue()) }}</td>
                            <td class="space-x-3 whitespace-nowrap text-right">
                                <a href="{{ route('products.show', $product) }}" class="text-sm font-semibold text-teal-600 hover:text-teal-800">Kartu Stok</a>
                                <a href="{{ route('products.edit', $product) }}" class="text-sm font-semibold text-slate-600 hover:text-gunmetal">Edit</a>
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="8" class="py-10 text-center text-slate-500">Tidak ada produk yang cocok dengan filter.</td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        @if ($products->hasPages())
            <div class="border-t border-slate-200 p-4">{{ $products->links() }}</div>
        @endif
    </div>
</x-app-layout>
