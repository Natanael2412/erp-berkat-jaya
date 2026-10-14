<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Neraca Saldo (Trial Balance)</h2>
        <p class="page-subtitle">Ringkasan saldo buku besar dari setiap akun hingga tanggal yang ditentukan.</p>
    </x-slot>

    <div class="card max-w-4xl mx-auto mb-6">
        <div class="card-header bg-slate-50">
            <form method="GET" action="{{ route('reports.trial-balance') }}" class="flex gap-3 items-end">
                <div>
                    <label class="label">Per Tanggal</label>
                    <input type="date" name="as_of" value="{{ $asOf }}" class="input w-48">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Tampilkan Neraca Saldo</button>
                </div>
            </form>
        </div>

        <div class="p-6 text-center border-b border-slate-200">
            <h1 class="text-2xl font-black text-gunmetal uppercase tracking-wider">ERP Berkat Jaya</h1>
            <h2 class="text-lg font-bold text-slate-600">Neraca Saldo</h2>
            <p class="text-sm text-slate-500">Per tanggal {{ \Carbon\Carbon::parse($asOf)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="table-wrap">
            <table class="table">
                <thead>
                    <tr>
                        <th class="w-24">Kode</th>
                        <th>Nama Akun</th>
                        <th class="text-right w-40">Debit</th>
                        <th class="text-right w-40">Kredit</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($rows as $row)
                        <tr class="hover:bg-slate-50 transition-colors">
                            <td class="font-mono font-medium text-slate-500">{{ $row->account->code }}</td>
                            <td class="font-bold text-slate-700">{{ $row->account->name }}</td>
                            <td class="num {{ $row->tb_debit > 0 ? 'text-teal-700 font-bold' : 'text-slate-400' }}">
                                {{ $row->tb_debit > 0 ? number_format($row->tb_debit, 0, ',', '.') : '-' }}
                            </td>
                            <td class="num {{ $row->tb_credit > 0 ? 'text-orange-700 font-bold' : 'text-slate-400' }}">
                                {{ $row->tb_credit > 0 ? number_format($row->tb_credit, 0, ',', '.') : '-' }}
                            </td>
                        </tr>
                    @empty
                        <tr>
                            <td colspan="4" class="py-8 text-center text-slate-500">
                                Tidak ada data saldo pada tanggal ini.
                            </td>
                        </tr>
                    @endforelse
                </tbody>
                <tfoot>
                    <tr class="bg-slate-50 border-t-4 border-slate-300">
                        <td colspan="2" class="text-right font-black uppercase tracking-wider text-slate-600">Total</td>
                        <td class="num text-lg font-black text-teal-800 border-b-4 border-double border-teal-800">
                            {{ number_format($total_debit, 0, ',', '.') }}
                        </td>
                        <td class="num text-lg font-black text-orange-800 border-b-4 border-double border-orange-800">
                            {{ number_format($total_credit, 0, ',', '.') }}
                        </td>
                    </tr>
                </tfoot>
            </table>
        </div>

        <div class="p-4 bg-slate-50 text-center">
            @if($balanced)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-sm font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Sisi Debit dan Kredit Seimbang (Balanced)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Terdapat Selisih Rp {{ number_format(abs($total_debit - $total_credit), 0, ',', '.') }}
                </span>
            @endif
        </div>
    </div>
</x-app-layout>
