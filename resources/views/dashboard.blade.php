<x-app-layout>
    <x-slot name="header">
        <h2 class="font-semibold text-xl text-gray-800 leading-tight">
            {{ __('Dashboard') }}
        </h2>
    </x-slot>

    <div class="py-12">
        <div class="max-w-7xl mx-auto sm:px-6 lg:px-8">
            
            <div class="grid grid-cols-1 md:grid-cols-2 gap-6 mb-6">
                <!-- Saldo Kas -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-l-4 border-brand-primary">
                        <h3 class="text-lg font-bold text-gray-500">Saldo Kas (Real-time)</h3>
                        <p class="text-3xl font-black text-brand-primary mt-2">Rp {{ number_format($saldoKas, 0, ',', '.') }}</p>
                    </div>
                </div>

                <!-- Laba/Rugi Harian -->
                <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                    <div class="p-6 text-gray-900 border-l-4 {{ $labaRugiHarian >= 0 ? 'border-brand-success' : 'border-brand-danger' }}">
                        <h3 class="text-lg font-bold text-gray-500">Laba / Rugi (Hari Ini)</h3>
                        <p class="text-3xl font-black {{ $labaRugiHarian >= 0 ? 'text-brand-success' : 'text-brand-danger' }} mt-2">
                            Rp {{ number_format($labaRugiHarian, 0, ',', '.') }}
                        </p>
                    </div>
                </div>
            </div>

            <!-- Mutasi Kas -->
            <div class="bg-white overflow-hidden shadow-sm sm:rounded-lg">
                <div class="p-6 text-gray-900">
                    <h3 class="text-xl font-bold mb-4">Mutasi Kas (10 Transaksi Terakhir)</h3>
                    <table class="w-full text-left border-collapse">
                        <thead>
                            <tr class="bg-gray-100">
                                <th class="p-3 border-b">Tanggal</th>
                                <th class="p-3 border-b">Deskripsi</th>
                                <th class="p-3 border-b text-right">Debit (Masuk)</th>
                                <th class="p-3 border-b text-right">Kredit (Keluar)</th>
                            </tr>
                        </thead>
                        <tbody>
                            @forelse($mutasiKas as $mutasi)
                            <tr>
                                <td class="p-3 border-b">{{ $mutasi->created_at->format('d/m/Y H:i') }}</td>
                                <td class="p-3 border-b">{{ $mutasi->journal->description }}</td>
                                <td class="p-3 border-b text-right text-brand-success">{{ $mutasi->debit_amount > 0 ? 'Rp ' . number_format($mutasi->debit_amount, 0, ',', '.') : '-' }}</td>
                                <td class="p-3 border-b text-right text-brand-danger">{{ $mutasi->credit_amount > 0 ? 'Rp ' . number_format($mutasi->credit_amount, 0, ',', '.') : '-' }}</td>
                            </tr>
                            @empty
                            <tr>
                                <td colspan="4" class="p-3 text-center text-gray-500">Belum ada mutasi kas.</td>
                            </tr>
                            @endforelse
                        </tbody>
                    </table>
                </div>
            </div>

        </div>
    </div>
</x-app-layout>
