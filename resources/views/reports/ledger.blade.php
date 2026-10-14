<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Buku Besar (Ledger)</h2>
        <p class="page-subtitle">Mutasi terperinci untuk akun tertentu.</p>
    </x-slot>

    <div class="card mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('reports.ledger') }}" class="flex flex-wrap gap-3 items-end w-full">
                <div>
                    <label class="label">Mulai Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}" class="input w-40">
                </div>
                <div>
                    <label class="label">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}" class="input w-40">
                </div>
                <div class="flex-1 min-w-[250px]">
                    <label class="label">Pilih Akun</label>
                    <select name="account_id" class="input">
                        @foreach($accounts as $acc)
                            <option value="{{ $acc->id }}" {{ $account->id == $acc->id ? 'selected' : '' }}>
                                {{ $acc->code }} - {{ $acc->name }} ({{ ucfirst($acc->normal_balance) }})
                            </option>
                        @endforeach
                    </select>
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Tampilkan Buku Besar</button>
                </div>
            </form>
        </div>
        
        <div class="grid grid-cols-1 md:grid-cols-3 border-b border-slate-200 divide-y md:divide-y-0 md:divide-x divide-slate-200">
            <div class="p-4 bg-white">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Saldo Awal ({{ \Carbon\Carbon::parse($from)->format('d/m/Y') }})</div>
                <div class="font-mono text-xl font-bold {{ $opening < 0 ? 'text-red-600' : 'text-gunmetal' }}">
                    Rp {{ number_format($opening, 0, ',', '.') }}
                </div>
            </div>
            <div class="p-4 bg-slate-50">
                <div class="text-[11px] font-bold uppercase tracking-wider text-slate-500 mb-1">Mutasi Periode Ini</div>
                <div class="flex gap-4">
                    <div class="text-sm"><span class="text-slate-400">D:</span> <span class="font-mono font-bold text-teal-700">{{ number_format($totalDebit, 0, ',', '.') }}</span></div>
                    <div class="text-sm"><span class="text-slate-400">K:</span> <span class="font-mono font-bold text-orange-700">{{ number_format($totalCredit, 0, ',', '.') }}</span></div>
                </div>
            </div>
            <div class="p-4 bg-teal-50/30">
                <div class="text-[11px] font-bold uppercase tracking-wider text-teal-800 mb-1">Saldo Akhir ({{ \Carbon\Carbon::parse($to)->format('d/m/Y') }})</div>
                <div class="font-mono text-xl font-bold text-teal-700">
                    Rp {{ number_format($closing, 0, ',', '.') }}
                </div>
            </div>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th>Tanggal</th>
                        <th>Jurnal & Keterangan</th>
                        <th class="text-right">Debit</th>
                        <th class="text-right">Kredit</th>
                        <th class="text-right">Saldo Berjalan</th>
                    </tr>
                </thead>
                <tbody>
                    <!-- Saldo Awal -->
                    <tr class="bg-slate-50/80">
                        <td class="text-slate-500">{{ \Carbon\Carbon::parse($from)->format('d/m/Y') }}</td>
                        <td class="font-bold text-slate-600">SALDO AWAL</td>
                        <td></td>
                        <td></td>
                        <td class="num font-bold text-gunmetal">Rp {{ number_format($opening, 0, ',', '.') }}</td>
                    </tr>
                    
                    <!-- Mutasi -->
                    @forelse($lines as $line)
                        <tr>
                            <td>{{ \Carbon\Carbon::parse($line->journal->journal_date)->format('d/m/Y') }}</td>
                            <td>
                                <div class="font-mono text-xs text-slate-500">{{ $line->journal->journal_number }}</div>
                                <div class="text-sm font-medium">{{ $line->journal->description }}</div>
                            </td>
                            <td class="num text-teal-700">{{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}</td>
                            <td class="num text-orange-700">{{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}</td>
                            <td class="num font-bold">{{ number_format($line->running_balance, 0, ',', '.') }}</td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="5" class="py-6 text-center text-slate-500">Tidak ada mutasi jurnal pada periode ini.</td>
                        </tr>
                    @endforelse

                    <!-- Saldo Akhir -->
                    <tr class="bg-slate-50/80 border-t-2 border-slate-300">
                        <td class="text-slate-500">{{ \Carbon\Carbon::parse($to)->format('d/m/Y') }}</td>
                        <td class="font-bold text-slate-600">SALDO AKHIR</td>
                        <td class="num font-bold text-teal-700">{{ number_format($totalDebit, 0, ',', '.') }}</td>
                        <td class="num font-bold text-orange-700">{{ number_format($totalCredit, 0, ',', '.') }}</td>
                        <td class="num text-lg font-black text-teal-800">Rp {{ number_format($closing, 0, ',', '.') }}</td>
                    </tr>
                </tbody>
            </table>
        </div>
    </div>
</x-app-layout>
