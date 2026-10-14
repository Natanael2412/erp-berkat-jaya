<x-app-layout>
    <x-slot name="header">
        <div class="flex justify-between items-center w-full">
            <div>
                <h2 class="page-title">{{ $supplier->exists ? 'Edit Supplier' : 'Tambah Supplier Baru' }}</h2>
                <p class="page-subtitle">{{ $supplier->exists ? 'Ubah informasi data supplier.' : 'Masukkan informasi supplier baru ke dalam sistem.' }}</p>
            </div>
            <a href="{{ route('suppliers.index') }}" class="btn btn-secondary">Batal & Kembali</a>
        </div>
    </x-slot>

    <div class="card max-w-2xl">
        <div class="card-header">
            <h3 class="card-title">Formulir Data Supplier</h3>
        </div>
        <div class="card-body">
            <form action="{{ $supplier->exists ? route('suppliers.update', $supplier) : route('suppliers.store') }}" method="POST">
                @csrf
                @if($supplier->exists)
                    @method('PUT')
                @endif
                
                <div class="space-y-5">
                    <div>
                        <label class="label">Kode Supplier <span class="text-red-500">*</span></label>
                        <input type="text" name="code" value="{{ old('code', $supplier->code) }}" class="input uppercase" required placeholder="Contoh: SUP-01" maxlength="20">
                        @error('code') <p class="input-error">{{ $message }}</p> @enderror
                        <p class="hint">Harus unik, maksimal 20 karakter.</p>
                    </div>

                    <div>
                        <label class="label">Nama Supplier / PT <span class="text-red-500">*</span></label>
                        <input type="text" name="name" value="{{ old('name', $supplier->name) }}" class="input" required placeholder="Contoh: PT Sumber Makmur">
                        @error('name') <p class="input-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Nomor Telepon / WhatsApp</label>
                        <input type="text" name="phone" value="{{ old('phone', $supplier->phone) }}" class="input" placeholder="Contoh: 08123456789">
                        @error('phone') <p class="input-error">{{ $message }}</p> @enderror
                    </div>

                    <div>
                        <label class="label">Alamat Lengkap</label>
                        <textarea name="address" rows="3" class="input" placeholder="Nama Jalan, RT/RW, Kota, Kode Pos">{{ old('address', $supplier->address) }}</textarea>
                        @error('address') <p class="input-error">{{ $message }}</p> @enderror
                    </div>

                    <div class="flex items-center gap-2 mt-4">
                        <input type="hidden" name="is_active" value="0">
                        <input type="checkbox" name="is_active" id="is_active" value="1" class="rounded border-slate-300 text-teal-600 focus:ring-teal-600" {{ old('is_active', $supplier->is_active) ? 'checked' : '' }}>
                        <label for="is_active" class="text-sm font-semibold text-slate-700 cursor-pointer">Supplier Aktif</label>
                    </div>
                </div>

                <div class="mt-8 pt-5 border-t border-slate-200">
                    <button type="submit" class="btn btn-primary w-full md:w-auto">
                        {{ $supplier->exists ? 'Simpan Perubahan' : 'Tambahkan Supplier' }}
                    </button>
                </div>
            </form>
        </div>
    </div>
</x-app-layout>
