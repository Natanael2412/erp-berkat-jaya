<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Jurnal Umum</h2>
        <p class="page-subtitle">Daftar entri jurnal ganda (double-entry) dari semua transaksi.</p>
    </x-slot>

    <div class="card mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('reports.journals') }}" class="flex flex-wrap gap-3 items-end w-full">
                <div>
                    <label class="label">Mulai Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}" class="input w-40">
                </div>
                <div>
                    <label class="label">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}" class="input w-40">
                </div>
                <div class="flex-1 min-w-[200px]">
                    <label class="label">Pencarian Jurnal / Keterangan</label>
                    <input type="text" name="q" value="{{ request('q') }}" placeholder="Contoh: JRN-2026..." class="input">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Tampilkan</button>
                    <a href="{{ route('reports.journals') }}" class="btn btn-ghost">Reset</a>
                </div>
            </form>
        </div>
        <div class="card-body bg-slate-50 border-b border-slate-200">
            <div class="flex flex-wrap gap-6 text-sm">
                <div>
                    <span class="text-slate-500 font-bold uppercase tracking-wider text-[11px]">Total Debit Jurnal:</span>
                    <span class="ml-2 font-mono font-bold text-gunmetal">Rp {{ number_format($totalDebit, 0, ',', '.') }}</span>
                </div>
                <div>
                    <span class="text-slate-500 font-bold uppercase tracking-wider text-[11px]">Total Kredit Jurnal:</span>
                    <span class="ml-2 font-mono font-bold text-gunmetal">Rp {{ number_format($totalCredit, 0, ',', '.') }}</span>
                </div>
                <div>
                    @if(abs($totalDebit - $totalCredit) < 0.01)
                        <span class="badge badge-green"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"></path></svg> Seimbang (Balanced)</span>
                    @else
                        <span class="badge badge-red"><svg class="w-3 h-3 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg> Tidak Seimbang (Unbalanced)</span>
                    @endif
                </div>
            </div>
        </div>
        
        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-1/6">Tgl & Ref</th>
                        <th class="w-1/2">Nama Akun & Keterangan</th>
                        <th class="text-right w-1/6">Debit</th>
                        <th class="text-right w-1/6">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($journals as $journal)
                        <tr class="bg-slate-50/50">
                            <td class="font-bold border-b-0 pb-0">
                                {{ \Carbon\Carbon::parse($journal->journal_date)->format('d/m/Y') }}
                            </td>
                            <td class="border-b-0 pb-0">
                                <span class="font-mono text-xs text-slate-500 bg-white px-2 py-0.5 rounded border border-slate-200">{{ $journal->journal_number }}</span>
                                @if($journal->reversalOf)
                                    <span class="badge badge-orange ml-2" title="Pembalik dari jurnal {{ $journal->reversalOf->journal_number }}">Reversal</span>
                                @endif
                                <div class="text-xs font-bold text-gunmetal mt-1.5">{{ $journal->description }}</div>
                            </td>
                            <td class="border-b-0 pb-0"></td>
                            <td class="border-b-0 pb-0"></td>
                        </tr>
                        @foreach($journal->lines as $line)
                        <tr>
                            <td></td>
                            <td>
                                <div class="flex items-center {{ $line->credit > 0 ? 'ml-8' : '' }}">
                                    <span class="font-mono text-[11px] text-slate-400 mr-2">{{ $line->account->code }}</span>
                                    <span class="{{ $line->credit > 0 ? 'text-slate-600' : 'font-bold text-gunmetal' }}">{{ $line->account->name }}</span>
                                </div>
                            </td>
                            <td class="num text-teal-700 {{ $line->debit > 0 ? 'font-bold' : '' }}">
                                {{ $line->debit > 0 ? number_format($line->debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="num text-orange-700 {{ $line->credit > 0 ? 'font-bold' : '' }}">
                                {{ $line->credit > 0 ? number_format($line->credit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                        @endforeach
                        <tr><td colspan="4" class="p-0 border-b-4 border-slate-100"></td></tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                Tidak ada entri jurnal yang ditemukan pada periode ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
        @if($journals->hasPages())
        <div class="border-t border-slate-200 p-4 bg-white">
            {{ $journals->links() }}
        </div>
        @endif
    </div>
</x-app-layout>
