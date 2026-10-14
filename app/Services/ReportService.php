<?php

namespace App\Services;

use App\Models\Account;
use App\Support\Accounts;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\DB;

/**
 * Laporan keuangan diturunkan 100% dari journal_lines (bukan dari tabel transaksi),
 * sehingga buku besar menjadi satu-satunya sumber angka laporan.
 */
class ReportService
{
    /**
     * Saldo setiap akun dalam rentang tanggal (inklusif).
     *
     * @return Collection<int, object{account: Account, debit: float, credit: float, balance: float}>
     */
    public function accountTotals(?string $from = null, ?string $to = null): Collection
    {
        $sums = DB::table('journal_lines')
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->when($from, fn ($q) => $q->where('journals.journal_date', '>=', $from))
            ->when($to, fn ($q) => $q->where('journals.journal_date', '<=', $to))
            ->groupBy('journal_lines.account_id')
            ->selectRaw('journal_lines.account_id, SUM(journal_lines.debit) AS d, SUM(journal_lines.credit) AS c')
            ->get()
            ->keyBy('account_id');

        return Account::orderBy('code')->get()->map(function (Account $a) use ($sums) {
            $d = round((float) ($sums[$a->id]->d ?? 0), 2);
            $c = round((float) ($sums[$a->id]->c ?? 0), 2);
            $net = $a->normal_balance === 'debit' ? $d - $c : $c - $d;

            return (object) ['account' => $a, 'debit' => $d, 'credit' => $c, 'balance' => round($net, 2)];
        });
    }

    /** Neraca Saldo: tiap akun ditampilkan di kolom debit atau kredit sesuai saldo bersihnya. */
    public function trialBalance(string $asOf): array
    {
        $rows = $this->accountTotals(null, $asOf)
            ->filter(fn ($r) => $r->debit != 0 || $r->credit != 0)
            ->map(function ($r) {
                $net = round($r->debit - $r->credit, 2);
                $r->tb_debit = $net > 0 ? $net : 0;
                $r->tb_credit = $net < 0 ? -$net : 0;

                return $r;
            })->values();

        $totalDebit = round($rows->sum('tb_debit'), 2);
        $totalCredit = round($rows->sum('tb_credit'), 2);

        return [
            'rows' => $rows,
            'total_debit' => $totalDebit,
            'total_credit' => $totalCredit,
            'balanced' => abs($totalDebit - $totalCredit) < 0.01,
        ];
    }

    /** Laporan Laba Rugi periode berjalan. */
    public function incomeStatement(string $from, string $to): array
    {
        $t = $this->accountTotals($from, $to)->keyBy(fn ($r) => $r->account->code);
        $bal = fn (string $code) => $t[$code]->balance ?? 0.0;

        $sales = $bal(Accounts::PENJUALAN);
        $salesReturns = $bal(Accounts::RETUR_PENJUALAN);
        $netSales = round($sales - $salesReturns, 2);
        $cogs = $bal(Accounts::HPP);
        $grossProfit = round($netSales - $cogs, 2);

        $expenses = $t->filter(fn ($r) => $r->account->type === 'expense' && $r->account->code !== Accounts::HPP && $r->balance != 0)->values();
        $totalExpenses = round($expenses->sum('balance'), 2);

        return [
            'sales' => $sales,
            'sales_returns' => $salesReturns,
            'net_sales' => $netSales,
            'cogs' => $cogs,
            'gross_profit' => $grossProfit,
            'expenses' => $expenses,
            'total_expenses' => $totalExpenses,
            'net_income' => round($grossProfit - $totalExpenses, 2),
        ];
    }

    /**
     * Laporan Posisi Keuangan (Neraca).
     * Belum ada jurnal penutup, sehingga laba berjalan = seluruh pendapatan - beban s.d. tanggal laporan.
     */
    public function balanceSheet(string $asOf): array
    {
        $t = $this->accountTotals(null, $asOf);

        $assets = $t->filter(fn ($r) => $r->account->type === 'asset')->values();
        $liabilities = $t->filter(fn ($r) => $r->account->type === 'liability')->values();
        $equity = $t->filter(fn ($r) => $r->account->type === 'equity')
            ->map(function ($r) {
                // Prive bersaldo normal debit: mengurangi ekuitas.
                $r->signed = $r->account->normal_balance === 'debit' ? -$r->balance : $r->balance;

                return $r;
            })->values();

        $revenue = $t->filter(fn ($r) => $r->account->type === 'revenue')
            ->sum(fn ($r) => $r->account->normal_balance === 'credit' ? $r->balance : -$r->balance);
        $expense = $t->filter(fn ($r) => $r->account->type === 'expense')->sum('balance');
        $currentEarnings = round($revenue - $expense, 2);

        $totalAssets = round($assets->sum('balance'), 2);
        $totalLiabilities = round($liabilities->sum('balance'), 2);
        $totalEquity = round($equity->sum('signed') + $currentEarnings, 2);

        return [
            'assets' => $assets,
            'liabilities' => $liabilities,
            'equity' => $equity,
            'current_earnings' => $currentEarnings,
            'total_assets' => $totalAssets,
            'total_liabilities' => $totalLiabilities,
            'total_equity' => $totalEquity,
            'balanced' => abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01,
        ];
    }
}
