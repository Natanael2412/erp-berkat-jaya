<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Laporan Laba Rugi (Income Statement)</h2>
        <p class="page-subtitle">Kinerja pendapatan dan beban pada periode tertentu.</p>
    </x-slot>

    <div class="card max-w-4xl mx-auto mb-6">
        <div class="card-header bg-slate-50 border-b border-slate-200">
            <form method="GET" action="{{ route('reports.income-statement') }}" class="flex flex-wrap gap-3 items-end">
                <div>
                    <label class="label">Mulai Tanggal</label>
                    <input type="date" name="from" value="{{ $from }}" class="input w-48">
                </div>
                <div>
                    <label class="label">Sampai Tanggal</label>
                    <input type="date" name="to" value="{{ $to }}" class="input w-48">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Tampilkan Laporan</button>
                </div>
            </form>
        </div>

        <div class="p-6 text-center border-b border-slate-200">
            <h1 class="text-2xl font-black text-gunmetal uppercase tracking-wider">ERP Berkat Jaya</h1>
            <h2 class="text-lg font-bold text-slate-600">Laporan Laba Rugi</h2>
            <p class="text-sm text-slate-500">Periode {{ \Carbon\Carbon::parse($from)->translatedFormat('d F Y') }} s/d {{ \Carbon\Carbon::parse($to)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="p-8 space-y-6">
            <!-- Pendapatan -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2 border-b border-slate-100 pb-1">Pendapatan Operasional</h3>
                <div class="flex justify-between py-1.5 px-2 hover:bg-slate-50">
                    <span class="text-slate-700">Penjualan Kotor</span>
                    <span class="num">Rp {{ number_format($sales, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-1.5 px-2 hover:bg-slate-50">
                    <span class="text-slate-700">Retur Penjualan</span>
                    <span class="num text-red-600">({{ number_format($sales_returns, 0, ',', '.') }})</span>
                </div>
                <div class="flex justify-between py-2 px-2 mt-2 bg-slate-50 font-bold border-t border-slate-200">
                    <span class="text-gunmetal">Penjualan Bersih</span>
                    <span class="num text-gunmetal">Rp {{ number_format($net_sales, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Harga Pokok Penjualan -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2 border-b border-slate-100 pb-1">Harga Pokok Penjualan (HPP)</h3>
                <div class="flex justify-between py-1.5 px-2 hover:bg-slate-50">
                    <span class="text-slate-700">Beban HPP</span>
                    <span class="num">Rp {{ number_format($cogs, 0, ',', '.') }}</span>
                </div>
                <div class="flex justify-between py-2 px-2 mt-2 bg-slate-50 font-bold border-t border-slate-200">
                    <span class="text-gunmetal">Laba Kotor</span>
                    <span class="num {{ $gross_profit >= 0 ? 'text-teal-700' : 'text-red-600' }}">Rp {{ number_format($gross_profit, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Beban Operasional -->
            <div>
                <h3 class="text-sm font-bold uppercase tracking-wider text-slate-400 mb-2 border-b border-slate-100 pb-1">Beban Operasional</h3>
                @forelse($expenses as $exp)
                    <div class="flex justify-between py-1.5 px-2 hover:bg-slate-50">
                        <span class="text-slate-700">{{ $exp->account->name }}</span>
                        <span class="num">Rp {{ number_format($exp->balance, 0, ',', '.') }}</span>
                    </div>
                @empty
                    <div class="py-2 text-slate-400 italic text-sm">Tidak ada beban operasional yang dicatat.</div>
                @endforelse
                <div class="flex justify-between py-2 px-2 mt-2 bg-slate-50 font-bold border-t border-slate-200">
                    <span class="text-gunmetal">Total Beban Operasional</span>
                    <span class="num text-orange-700">Rp {{ number_format($total_expenses, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Laba Bersih -->
            <div class="mt-8 pt-4 border-t-4 border-slate-300">
                <div class="flex justify-between items-center p-4 rounded-lg {{ $net_income >= 0 ? 'bg-emerald-50' : 'bg-red-50' }}">
                    <span class="text-lg font-black uppercase tracking-widest {{ $net_income >= 0 ? 'text-emerald-900' : 'text-red-900' }}">
                        {{ $net_income >= 0 ? 'Laba Bersih' : 'Rugi Bersih' }}
                    </span>
                    <span class="font-mono text-2xl font-black {{ $net_income >= 0 ? 'text-emerald-700' : 'text-red-700' }}">
                        Rp {{ number_format($net_income, 0, ',', '.') }}
                    </span>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
