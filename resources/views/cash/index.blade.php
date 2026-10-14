<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Arus Kas & Beban</h2>
        <p class="page-subtitle">Kelola pencatatan beban operasional, setoran modal, dan penarikan prive (tunai/bank).</p>
    </x-slot>

    <!-- Ringkasan Saldo -->
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4 mb-6">
        @foreach($cashAccounts as $account)
        <div class="kpi">
            <div class="kpi-label">{{ $account->name }} ({{ $account->code }})</div>
            <div class="kpi-value text-teal-700">Rp {{ number_format($balances[$account->id] ?? 0, 0, ',', '.') }}</div>
            <div class="kpi-foot">Saldo Berjalan</div>
        </div>
        @endforeach
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        
        <!-- Form Catat Transaksi Baru -->
        <div class="lg:col-span-1">
            <div class="card">
                <div class="card-header">
                    <h3 class="card-title">Catat Transaksi Baru</h3>
                </div>
                <div class="card-body">
                    <form method="POST" action="{{ route('cash.store') }}">
                        @csrf
                        <div class="space-y-4">
                            <div>
                                <label class="label">Jenis Transaksi</label>
                                <select name="type" class="input" required>
                                    <option value="" disabled selected>-- Pilih Jenis --</option>
                                    @foreach(\App\Models\CashTransaction::TYPES as $val => $label)
                                        <option value="{{ $val }}" {{ old('type') === $val ? 'selected' : '' }}>{{ $label }}</option>
                                    @endforeach
                                </select>
                                @error('type') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label">Pilih Akun Kas/Bank</label>
                                <select name="cash_account_id" class="input" required>
                                    <option value="" disabled selected>-- Pilih Akun --</option>
                                    @foreach($cashAccounts as $acc)
                                        <option value="{{ $acc->id }}" {{ old('cash_account_id') == $acc->id ? 'selected' : '' }}>
                                            {{ $acc->code }} - {{ $acc->name }}
                                        </option>
                                    @endforeach
                                </select>
                                @error('cash_account_id') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label">Tanggal Transaksi</label>
                                <input type="date" name="transaction_date" value="{{ old('transaction_date', today()->format('Y-m-d')) }}" max="{{ today()->format('Y-m-d') }}" class="input" required>
                                @error('transaction_date') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label">Nominal (Rp)</label>
                                <input type="number" name="amount" value="{{ old('amount') }}" min="1" step="1" class="input" placeholder="0" required>
                                @error('amount') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <div>
                                <label class="label">Keterangan / Deskripsi</label>
                                <textarea name="description" rows="3" class="input" placeholder="Contoh: Bayar listrik bulan Oktober..." required>{{ old('description') }}</textarea>
                                @error('description') <p class="input-error">{{ $message }}</p> @enderror
                            </div>

                            <button type="submit" class="btn btn-primary w-full">Simpan Transaksi</button>
                        </div>
                    </form>
                </div>
            </div>
        </div>

        <!-- Tabel Riwayat -->
        <div class="lg:col-span-2">
            <div class="card h-full">
                <div class="card-header">
                    <h3 class="card-title">Riwayat Transaksi Kas</h3>
                </div>
                <div class="table-wrap">
                    <table class="table">
                        <thead>
                            <tr>
                                <th>Tanggal</th>
                                <th>Ref & Akun</th>
                                <th>Jenis</th>
                                <th>Keterangan</th>
                                <th class="text-right">Nominal</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($transactions as $trx)
                            <tr>
                                <td>{{ $trx->transaction_date->format('d/m/Y') }}</td>
                                <td>
                                    <div class="font-mono text-xs text-gunmetal">{{ $trx->transaction_number }}</div>
                                    <div class="font-bold">{{ $trx->cashAccount->name ?? '-' }}</div>
                                </td>
                                <td>
                                    @if($trx->isInflow())
                                        <span class="badge badge-green">{{ $trx->typeLabel() }}</span>
                                    @else
                                        <span class="badge badge-red">{{ $trx->typeLabel() }}</span>
                                    @endif
                                </td>
                                <td>
                                    <div class="text-xs text-gunmetal mb-0.5">{{ Str::limit($trx->description, 50) }}</div>
                                    <div class="text-[10px] text-slate-400">Oleh: {{ $trx->creator->name ?? 'Sistem' }}</div>
                                </td>
                                <td class="num {{ $trx->isInflow() ? 'text-emerald-600' : 'text-red-600' }} font-bold">
                                    {{ $trx->isInflow() ? '+' : '-' }} Rp {{ number_format($trx->amount, 0, ',', '.') }}
                                </td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="5" class="py-8 text-center text-slate-500">
                                    Belum ada transaksi kas yang dicatat.
                                </td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
                @if($transactions->hasPages())
                <div class="border-t border-slate-200 p-4">
                    {{ $transactions->links() }}
                </div>
                @endif
            </div>
        </div>

    </div>
</x-app-layout>
