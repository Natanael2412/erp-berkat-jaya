<x-app-layout>
    @php
        $editing = $product->exists;
        $baseUnitId = old('base_unit_id', $product->base_unit_id);
        $initialUnits = old('units') ?? ($editing
            ? $product->units->where('is_active', true)->where('unit_id', '!=', $product->base_unit_id)
                ->map(fn ($u) => [
                    'unit_id' => $u->unit_id,
                    'conversion_factor' => (float) $u->conversion_factor,
                    'selling_price' => (float) $u->selling_price,
                ])->values()->all()
            : []);
        $baseSelling = old('base_selling_price', $editing ? (float) optional($product->baseProductUnit())->selling_price : '');
    @endphp

    <x-slot name="header">
        <div class="flex w-full items-center justify-between gap-3">
            <div>
                <h2 class="page-title">{{ $editing ? 'Edit Produk' : 'Tambah Produk Baru' }}</h2>
                <p class="page-subtitle">
                    {{ $editing ? $product->sku.' · '.$product->name : 'Tentukan satuan dasar, konversi satuan, harga jual, dan stok awal.' }}
                </p>
            </div>
            <a href="{{ $editing ? route('products.show', $product) : route('products.index') }}" class="btn btn-secondary">Batal &amp; Kembali</a>
        </div>
    </x-slot>

    <form method="POST" action="{{ $editing ? route('products.update', $product) : route('products.store') }}"
          x-data="productForm({{ \Illuminate\Support\Js::from($initialUnits) }}, {{ \Illuminate\Support\Js::from($units->pluck('name', 'id')) }}, {{ (int) $baseUnitId }})"
          class="grid gap-6 lg:grid-cols-3">
        @csrf
        @if ($editing) @method('PUT') @endif

        <div class="space-y-6 lg:col-span-2">
            {{-- Data utama --}}
            <div class="card">
                <div class="card-header"><h3 class="card-title">Data Produk</h3></div>
                <div class="card-body grid gap-5 md:grid-cols-2">
                    <div>
                        <label class="label" for="sku">SKU <span class="text-red-500">*</span></label>
                        <input id="sku" type="text" name="sku" value="{{ old('sku', $product->sku) }}" class="input font-mono uppercase" maxlength="50" required placeholder="KBL-NYM-2X15">
                        @error('sku') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="name">Nama Produk <span class="text-red-500">*</span></label>
                        <input id="name" type="text" name="name" value="{{ old('name', $product->name) }}" class="input" maxlength="150" required placeholder="Kabel NYM 2x1.5mm">
                        @error('name') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                    <div>
                        <label class="label" for="base_unit_id">Satuan Dasar <span class="text-red-500">*</span></label>
                        @if ($editing)
                            <input type="text" class="input" value="{{ $product->baseUnit->name }}" disabled>
                            <p class="hint">Satuan dasar tidak bisa diubah karena seluruh stok dinyatakan dalam satuan ini.</p>
                        @else
                            <select id="base_unit_id" name="base_unit_id" class="input" x-model.number="baseUnit" required>
                                <option value="">Pilih satuan…</option>
                                @foreach ($units as $u)
                                    <option value="{{ $u->id }}">{{ $u->name }} ({{ $u->code }})</option>
                                @endforeach
                            </select>
                            @error('base_unit_id') <p class="input-error">{{ $message }}</p> @enderror
                            <p class="hint">Satuan terkecil yang dijual, mis. Meter atau Pcs.</p>
                        @endif
                    </div>
                    <div>
                        <label class="label" for="min_stock">Stok Minimum</label>
                        <input id="min_stock" type="number" step="0.0001" min="0" name="min_stock" value="{{ old('min_stock', (float) $product->min_stock) }}" class="input num text-left">
                        @error('min_stock') <p class="input-error">{{ $message }}</p> @enderror
                        <p class="hint">Peringatan muncul bila stok &le; angka ini (dalam satuan dasar).</p>
                    </div>
                    <div class="md:col-span-2">
                        <label class="label" for="base_selling_price">Harga Jual per Satuan Dasar (Rp) <span class="text-red-500">*</span></label>
                        <input id="base_selling_price" type="number" step="0.01" min="0" name="base_selling_price" value="{{ $baseSelling }}" class="input" required placeholder="6500">
                        @error('base_selling_price') <p class="input-error">{{ $message }}</p> @enderror
                    </div>
                </div>
            </div>

            {{-- Konversi satuan --}}
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Satuan Lain &amp; Konversi</h3>
                    <button type="button" class="btn btn-secondary btn-sm" @click="add()" id="btn-add-unit">+ Tambah Satuan</button>
                </div>
                <div class="card-body space-y-3">
                    <template x-for="(row, i) in rows" :key="i">
                        <div class="grid gap-3 rounded-lg border border-slate-200 bg-slate-50 p-4 md:grid-cols-12 md:items-end">
                            <div class="md:col-span-4">
                                <label class="label">Satuan</label>
                                <select :name="`units[${i}][unit_id]`" x-model.number="row.unit_id" class="input" required>
                                    <option value="">Pilih…</option>
                                    <template x-for="(name, id) in unitNames" :key="id">
                                        <option :value="id" x-text="name" :disabled="id == baseUnit"></option>
                                    </template>
                                </select>
                            </div>
                            <div class="md:col-span-3">
                                <label class="label">Isi (satuan dasar)</label>
                                <input type="number" step="0.0001" min="1.0001" :name="`units[${i}][conversion_factor]`" x-model="row.conversion_factor" class="input" required>
                            </div>
                            <div class="md:col-span-4">
                                <label class="label">Harga Jual (Rp)</label>
                                <input type="number" step="0.01" min="0" :name="`units[${i}][selling_price]`" x-model="row.selling_price" class="input" required>
                            </div>
                            <div class="md:col-span-1">
                                <button type="button" class="btn btn-ghost btn-sm w-full text-red-600" @click="remove(i)" aria-label="Hapus satuan">&times;</button>
                            </div>
                            <p class="hint md:col-span-12" x-show="row.unit_id && row.conversion_factor">
                                1 <span x-text="unitNames[row.unit_id]"></span> = <span x-text="row.conversion_factor"></span> <span x-text="unitNames[baseUnit] || 'satuan dasar'"></span>
                            </p>
                        </div>
                    </template>
                    <p class="text-sm text-slate-500" x-show="rows.length === 0">Belum ada satuan lain. Produk hanya dijual dalam satuan dasar.</p>
                    @if ($editing)
                        <p class="hint">Satuan yang dihapus akan dinonaktifkan (bukan dihapus) agar riwayat transaksi tetap utuh.</p>
                    @endif
                </div>
            </div>
        </div>

        {{-- Sisi kanan --}}
        <div class="space-y-6">
            @unless ($editing)
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Stok Awal (Opsional)</h3></div>
                    <div class="card-body space-y-4">
                        <div>
                            <label class="label" for="opening_qty">Qty Stok Awal</label>
                            <input id="opening_qty" type="number" step="0.0001" min="0" name="opening_qty" value="{{ old('opening_qty') }}" class="input">
                            @error('opening_qty') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label" for="opening_cost">HPP Awal per Satuan Dasar (Rp)</label>
                            <input id="opening_cost" type="number" step="0.01" min="0" name="opening_cost" value="{{ old('opening_cost') }}" class="input">
                            @error('opening_cost') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <div>
                            <label class="label" for="opening_date">Tanggal</label>
                            <input id="opening_date" type="date" name="opening_date" max="{{ today()->toDateString() }}" value="{{ old('opening_date', today()->toDateString()) }}" class="input">
                            @error('opening_date') <p class="input-error">{{ $message }}</p> @enderror
                        </div>
                        <p class="hint">Dijurnal otomatis: Persediaan (D) / Modal Pemilik (K).</p>
                    </div>
                </div>
            @else
                <div class="card">
                    <div class="card-header"><h3 class="card-title">Status</h3></div>
                    <div class="card-body">
                        <input type="hidden" name="is_active" value="0">
                        <label class="flex cursor-pointer items-center gap-2 text-sm font-semibold text-slate-700">
                            <input type="checkbox" name="is_active" value="1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-600" @checked(old('is_active', $product->is_active))>
                            Produk aktif (tampil di POS &amp; PO)
                        </label>
                        <p class="hint mt-3">Stok saat ini: <strong>{{ num($product->current_stock) }} {{ $product->baseUnit->name }}</strong>. Stok hanya berubah lewat transaksi agar kartu stok tetap akurat.</p>
                    </div>
                </div>
            @endunless

            <button type="submit" class="btn btn-primary btn-lg w-full" id="btn-save-product">
                {{ $editing ? 'Simpan Perubahan' : 'Simpan Produk' }}
            </button>
        </div>
    </form>

    <script>
        function productForm(initial, unitNames, baseUnit) {
            return {
                rows: initial,
                unitNames,
                baseUnit,
                add() { this.rows.push({ unit_id: '', conversion_factor: 2, selling_price: 0 }); },
                remove(i) { this.rows.splice(i, 1); },
            };
        }
    </script>
</x-app-layout>
