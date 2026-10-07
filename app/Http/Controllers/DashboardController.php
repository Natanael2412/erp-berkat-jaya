<?php

namespace App\Http\Controllers;

use App\Models\JournalLine;
use Illuminate\Http\Request;
use Carbon\Carbon;

class DashboardController extends Controller
{
    public function index(Request $request)
    {
        $today = Carbon::today();

        // Saldo Kas (Asumsi '1110')
        $kasDebit = JournalLine::where('account_code', '1110')->sum('debit_amount');
        $kasCredit = JournalLine::where('account_code', '1110')->sum('credit_amount');
        $saldoKas = $kasDebit - $kasCredit;

        // Laba / Rugi Harian
        // Pendapatan ('4110') Credit is positive revenue
        $pendapatanHariIni = JournalLine::whereHas('journal', function($q) use ($today) {
                $q->whereDate('date', $today);
            })->where('account_code', '4110')->sum('credit_amount');

        // HPP ('5110') Debit is expense
        $hppHariIni = JournalLine::whereHas('journal', function($q) use ($today) {
                $q->whereDate('date', $today);
            })->where('account_code', '5110')->sum('debit_amount');
            
        $labaRugiHarian = $pendapatanHariIni - $hppHariIni;

        // Mutasi Kas (10 transaksi terakhir)
        $mutasiKas = JournalLine::with('journal')
            ->where('account_code', '1110')
            ->latest()
            ->take(10)
            ->get();

        return view('dashboard', compact('saldoKas', 'labaRugiHarian', 'mutasiKas'));
    }
}
