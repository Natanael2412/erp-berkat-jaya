<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Satuan Barang</h2>
        <p class="page-subtitle">Kelola master data satuan (PCS, KG, BOX) untuk produk.</p>
    </x-slot>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        <!-- Form Tambah/Edit Satuan -->
        <div class="lg:col-span-1">
            <div class="card" x-data="{ 
                isEdit: false, 
                id: '', 
                code: '', 
                name: '',
                initEdit(unit) {
                    this.isEdit = true;
                    this.id = unit.id;
                    this.code = unit.code;
                    this.name = unit.name;
                    $refs.form.action = '/units/' + unit.id;
                    $refs.method.value = 'PUT';
                    window.scrollTo({ top: 0, behavior: 'smooth' });
                },
                resetForm() {
                    this.isEdit = false;
                    this.id = '';
                    this.code = '';
                    this.name = '';
                    $refs.form.action = '{{ route('units.store') }}';
                    $refs.method.value = 'POST';
                }
            }">
                <div class="card-header flex justify-between items-center">
                    <h3 class="card-title" x-text="isEdit ? 'Edit Satuan' : 'Tambah Satuan'"></h3>
                    <button x-show="isEdit" @click="resetForm()" type="button" class="text-xs text-slate-500 hover:text-red-500 font-bold uppercase tracking-wider" x-cloak>Batal Edit</button>
                </div>
                <div class="card-body">
                    <form x-ref="form" action="{{ route('units.store') }}" method="POST">
                        @csrf
                        <input type="hidden" name="_method" value="POST" x-ref="method">
                        
                        <div class="space-y-4">
                            <div>
                                <label class="label">Kode Satuan <span class="text-red-500">*</span></label>
                                <input type="text" name="code" x-model="code" class="input uppercase" required placeholder="Contoh: PCS">
                                @error('code') <p class="input-error">{{ $message }}</p> @enderror
                            </div>
                            
                            <div>
                                <label class="label">Nama Lengkap <span class="text-red-500">*</span></label>
                                <input type="text" name="name" x-model="name" class="input" required placeholder="Contoh: Pieces">
                                @error('name') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="btn w-full" :class="isEdit ? 'btn-warning' : 'btn-primary'" x-text="isEdit ? 'Simpan Perubahan' : 'Tambahkan Satuan'"></button>
                        </div>
                    </form>
                </div>
                
                <!-- Helper Data for Alpine -->
                <div class="hidden">
                    @foreach($units as $unit)
                        <button id="edit-btn-{{ $unit->id }}" @click="initEdit({{ json_encode(['id' => $unit->id, 'code' => $unit->code, 'name' => $unit->name]) }})"></button>
                    @endforeach
                </div>
            </div>
        </div>

        <!-- Tabel Daftar Satuan -->
        <div class="lg:col-span-2">
            <div class="card h-full">
                <div class="card-header">
                    <h3 class="card-title">Daftar Satuan</h3>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Kode</th>
                                <th>Nama Satuan</th>
                                <th class="text-right">Digunakan Di Produk</th>
                                <th class="text-right">Aksi</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($units as $unit)
                            <tr>
                                <td class="font-mono font-bold">{{ $unit->code }}</td>
                                <td class="font-bold text-gunmetal">{{ $unit->name }}</td>
                                <td class="num">{{ $unit->product_units_count }}</td>
                                <td class="text-right">
                                    <button onclick="document.getElementById('edit-btn-{{ $unit->id }}').click()" class="text-sm font-semibold text-orange-600 hover:text-orange-800">Edit</button>
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="py-8 text-center text-slate-500">
                                    Belum ada data satuan. Silakan tambahkan di form sebelah.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
        </div>

    </div>
</x-app-layout>
