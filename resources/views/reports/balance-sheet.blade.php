<x-app-layout>
    <x-slot name="header">
        <h2 class="page-title">Laporan Posisi Keuangan (Neraca)</h2>
        <p class="page-subtitle">Posisi aset, liabilitas, dan ekuitas perusahaan per tanggal tertentu.</p>
    </x-slot>

    <div class="card max-w-5xl mx-auto mb-6">
        <div class="card-header bg-slate-50 border-b border-slate-200">
            <form method="GET" action="{{ route('reports.balance-sheet') }}" class="flex gap-3 items-end">
                <div>
                    <label class="label">Per Tanggal</label>
                    <input type="date" name="as_of" value="{{ $asOf }}" class="input w-48">
                </div>
                <div>
                    <button type="submit" class="btn btn-dark">Tampilkan Neraca</button>
                </div>
            </form>
        </div>

        <div class="p-6 text-center border-b border-slate-200">
            <h1 class="text-2xl font-black text-gunmetal uppercase tracking-wider">ERP Berkat Jaya</h1>
            <h2 class="text-lg font-bold text-slate-600">Laporan Posisi Keuangan</h2>
            <p class="text-sm text-slate-500">Per tanggal {{ \Carbon\Carbon::parse($asOf)->translatedFormat('d F Y') }}</p>
        </div>

        <div class="p-8 grid grid-cols-1 md:grid-cols-2 gap-12">
            <!-- Kolom Kiri: Aset -->
            <div>
                <h3 class="text-lg font-black uppercase tracking-wider text-gunmetal mb-4 border-b-2 border-gunmetal pb-2">Aset (Harta)</h3>
                
                <div class="space-y-1 mb-6">
                    @forelse($assets as $asset)
                        <div class="flex justify-between py-1 px-1 hover:bg-slate-50">
                            <span class="text-slate-700">{{ $asset->account->name }}</span>
                            <span class="num">Rp {{ number_format($asset->balance, 0, ',', '.') }}</span>
                        </div>
                    @empty
                        <div class="py-2 text-slate-400 italic text-sm">Tidak ada saldo aset.</div>
                    @endforelse
                </div>

                <div class="flex justify-between p-3 mt-4 bg-teal-50 border-t-2 border-teal-200 rounded-b">
                    <span class="font-black uppercase tracking-wider text-teal-900">Total Aset</span>
                    <span class="num text-lg font-black text-teal-800">Rp {{ number_format($total_assets, 0, ',', '.') }}</span>
                </div>
            </div>

            <!-- Kolom Kanan: Kewajiban & Ekuitas -->
            <div>
                <!-- Kewajiban (Liabilitas) -->
                <div class="mb-8">
                    <h3 class="text-lg font-black uppercase tracking-wider text-gunmetal mb-4 border-b-2 border-gunmetal pb-2">Kewajiban (Utang)</h3>
                    <div class="space-y-1 mb-4">
                        @forelse($liabilities as $liability)
                            <div class="flex justify-between py-1 px-1 hover:bg-slate-50">
                                <span class="text-slate-700">{{ $liability->account->name }}</span>
                                <span class="num">Rp {{ number_format($liability->balance, 0, ',', '.') }}</span>
                            </div>
                        @empty
                            <div class="py-2 text-slate-400 italic text-sm">Tidak ada saldo kewajiban.</div>
                        @endforelse
                    </div>
                    <div class="flex justify-between p-2 bg-slate-50 border-t border-slate-200 font-bold">
                        <span class="text-slate-700">Total Kewajiban</span>
                        <span class="num text-slate-800">Rp {{ number_format($total_liabilities, 0, ',', '.') }}</span>
                    </div>
                </div>

                <!-- Ekuitas (Modal) -->
                <div>
                    <h3 class="text-lg font-black uppercase tracking-wider text-gunmetal mb-4 border-b-2 border-gunmetal pb-2">Ekuitas (Modal)</h3>
                    <div class="space-y-1 mb-4">
                        @forelse($equity as $eq)
                            <div class="flex justify-between py-1 px-1 hover:bg-slate-50">
                                <span class="text-slate-700">{{ $eq->account->name }}</span>
                                <span class="num {{ $eq->signed < 0 ? 'text-red-600' : '' }}">
                                    {{ $eq->signed < 0 ? '(' : '' }}Rp {{ number_format(abs($eq->signed), 0, ',', '.') }}{{ $eq->signed < 0 ? ')' : '' }}
                                </span>
                            </div>
                        @empty
                            <div class="py-2 text-slate-400 italic text-sm">Tidak ada saldo ekuitas.</div>
                        @endforelse
                        <div class="flex justify-between py-1 px-1 hover:bg-slate-50">
                            <span class="text-slate-700 font-semibold text-teal-700">Laba/(Rugi) Berjalan</span>
                            <span class="num font-bold {{ $current_earnings < 0 ? 'text-red-600' : 'text-teal-700' }}">
                                {{ $current_earnings < 0 ? '(' : '' }}Rp {{ number_format(abs($current_earnings), 0, ',', '.') }}{{ $current_earnings < 0 ? ')' : '' }}
                            </span>
                        </div>
                    </div>
                    <div class="flex justify-between p-2 bg-slate-50 border-t border-slate-200 font-bold">
                        <span class="text-slate-700">Total Ekuitas</span>
                        <span class="num text-slate-800">Rp {{ number_format($total_equity, 0, ',', '.') }}</span>
                    </div>
                </div>

                <div class="flex justify-between p-3 mt-6 bg-orange-50 border-t-2 border-orange-200 rounded-b mt-auto">
                    <span class="font-black uppercase tracking-wider text-orange-900">Total Kewajiban & Ekuitas</span>
                    <span class="num text-lg font-black text-orange-800">Rp {{ number_format($total_liabilities + $total_equity, 0, ',', '.') }}</span>
                </div>
            </div>
        </div>

        <div class="p-4 bg-slate-100 text-center rounded-b-lg border-t border-slate-200">
            @if($balanced)
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-emerald-100 text-emerald-800 text-sm font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"></path></svg>
                    Neraca Seimbang (Aset = Kewajiban + Ekuitas)
                </span>
            @else
                <span class="inline-flex items-center gap-1.5 px-3 py-1 rounded-full bg-red-100 text-red-800 text-sm font-bold">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path></svg>
                    Neraca Tidak Seimbang! Selisih: Rp {{ number_format(abs($total_assets - ($total_liabilities + $total_equity)), 0, ',', '.') }}
                </span>
            @endif
        </div>
    </div>
</x-app-layout>
