<x-app-layout>
    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">Buat Purchase Order</h2>
                <p class="page-subtitle">Pesan barang ke supplier. Stok &amp; utang baru tercatat saat barang diterima.</p>
            </div>
            <a href="{{ route('purchase-orders.index') }}" class="btn btn-secondary">Batal &amp; Kembali</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ route('purchase-orders.store') }}" x-data="poForm(@js($products), @js(old('items', [])))" class="grid gap-6 lg:grid-cols-3">
        @csrf

        <div class="space-y-6 lg:col-span-2">
            <div class="card">
                <div class="card-header"><h3 class="card-title">Informasi Pesanan</h3></div>
                <div class="card-body grid gap-5 md:grid-cols-2">
                    <div class="md:col-span-2">
                        <label class="label" for="supplier_id">Supplier <span class="text-red-500">*</span></label>
                        <select id="supplier_id" name="supplier_id" class="input" required>
                            <option value="">Pilih supplier…</option>
                            @foreach ($suppliers as $s)
                                <option value="{{ $s->id }}" @selected(old('supplier_id') == $s->id)>{{ $s->code }} — {{ $s->name }}</option>
                            @endforeach
                        </select>
                        @error('supplier_id') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="order_date">Tanggal Pesanan <span class="text-red-500">*</span></label>
                        <input id="order_date" type="date" name="order_date" max="{{ today()->toDateString() }}" value="{{ old('order_date', today()->toDateString()) }}" class="input" required>
                        @error('order_date') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="notes">Catatan</label>
                        <input id="notes" type="text" name="notes" maxlength="255" value="{{ old('notes') }}" class="input" placeholder="Opsional">
                        @error('notes') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Barang yang Dipesan</h3>
                    <button type="button" class="btn btn-secondary btn-sm" @click="add()" id="btn-add-item">+ Tambah Barang</button>
                </div>
                <div class="card-body space-y-3">
                    <template x-for="(row, i) in rows" :key="row.key">
                        <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-12 md:items-end">
                            <div class="md:col-span-4">
                                <label class="label">Produk</label>
                                <select class="input" x-model.number="row.product_id" @change="row.product_unit_id = ''; row.unit_price = ''" required>
                                    <option value="">Pilih produk…</option>
                                    <template x-for="p in products" :key="p.id">
                                        <option :value="p.id" x-text="p.label"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="label">Satuan Beli</label>
                                <select class="input" :name="`items[${i}][product_unit_id]`" x-model.number="row.product_unit_id" required>
                                    <option value="">Pilih…</option>
                                    <template x-for="u in unitsOf(row.product_id)" :key="u.id">
                                        <option :value="u.id" x-text="u.name + (u.factor != 1 ? ' (' + u.factor + ')' : '')"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-2">
                                <label class="label">Qty</label>
                                <input type="number" step="0.0001" min="0.0001" class="input" :name="`items[${i}][qty_ordered]`" x-model="row.qty_ordered" required>
                            </div>
                            <div class="md:col-span-3">
                                <label class="label">Harga Beli / Satuan</label>
                                <input type="number" step="0.01" min="0" class="input" :name="`items[${i}][unit_price]`" x-model="row.unit_price" required>
                            </div>
                            <div class="md:col-span-1">
                                <button type="button" class="btn btn-ghost btn-sm w-full text-red-600" @click="remove(i)" aria-label="Hapus baris">&times;</button>
                            </div>
                            <p class="hint md:col-span-12" x-show="hint(row)" x-text="hint(row)"></p>
                        </div>
                    </template>
                    <p class="text-sm font-semibold text-red-600" x-show="rows.length === 0">Tambahkan minimal satu barang.</p>
                </div>
            </div>
        </div>

        <div>
            <div class="card sticky top-24">
                <div class="card-header"><h3 class="card-title">Ringkasan</h3></div>
                <div class="card-body space-y-4">
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-slate-500">Jumlah baris</span>
                        <span class="font-bold" x-text="rows.length"></span>
                    </div>
                    <div class="border-t border-slate-200 pt-4">
                        <div class="kpi-label">Estimasi Total PO</div>
                        <div class="kpi-value" x-text="rupiah(total())"></div>
                    </div>
                    <button type="submit" class="btn btn-primary btn-lg w-full" :disabled="rows.length === 0" id="btn-save-po">Buat Purchase Order</button>
                    <p class="hint">Stok &amp; utang diakui saat penerimaan barang, bukan saat PO dibuat.</p>
                </div>
            </div>
        </div>
    </form>

    <script>
        function poForm(products, old) {
            let seq = 0;
            const findProductId = (unitId) => (products.find(p => p.units.some(u => u.id == unitId)) || {}).id || '';
            const make = (r = {}) => ({
                key: ++seq,
                product_id: r.product_unit_id ? findProductId(r.product_unit_id) : '',
                product_unit_id: r.product_unit_id || '',
                qty_ordered: r.qty_ordered ?? 1,
                unit_price: r.unit_price ?? '',
            });
            return {
                products,
                rows: (old && old.length ? old : [{}]).map(make),
                add() { this.rows.push(make()); },
                remove(i) { this.rows.splice(i, 1); },
                unitsOf(pid) { const p = this.products.find(x => x.id == pid); return p ? p.units : []; },
                hint(row) {
                    const p = this.products.find(x => x.id == row.product_id);
                    const u = p && p.units.find(x => x.id == row.product_unit_id);
                    if (!p || !u) return '';
                    const cost = Number(row.unit_price) > 0 ? ' · biaya per satuan dasar ≈ ' + this.rupiah(Number(row.unit_price) / u.factor) : '';
                    return 'HPP saat ini: ' + this.rupiah(p.hpp) + ' per satuan dasar' + cost;
                },
                total() { return this.rows.reduce((s, r) => s + (Number(r.qty_ordered) || 0) * (Number(r.unit_price) || 0), 0); },
                rupiah(n) { return 'Rp ' + new Intl.NumberFormat('id-ID', { maximumFractionDigits: 0 }).format(n); },
            };
        }
    </script>
</x-app-layout>
