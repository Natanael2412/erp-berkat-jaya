<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\Journal;
use App\Models\JournalLine;
use App\Services\ReportService;
use Illuminate\Http\Request;

class ReportController extends Controller
{
    public function __construct(private ReportService $reports) {}

    /** Jurnal Umum: semua jurnal beserta barisnya, untuk membuktikan setiap entri seimbang. */
    public function journals(Request $request)
    {
        [$from, $to] = $this->period($request);

        $base = Journal::whereBetween('journal_date', [$from, $to])
            ->when($request->q, fn ($q, $s) => $q->where(fn ($w) => $w->where('journal_number', 'like', "%{$s}%")->orWhere('description', 'like', "%{$s}%")));

        $journals = (clone $base)->with(['lines.account', 'reversalOf'])
            ->orderBy('journal_date')->orderBy('id')
            ->paginate(30)->withQueryString();

        $totals = JournalLine::whereIn('journal_id', (clone $base)->select('id'))
            ->selectRaw('COALESCE(SUM(debit),0) d, COALESCE(SUM(credit),0) c')->first();

        return view('reports.journals', [
            'journals' => $journals,
            'from' => $from,
            'to' => $to,
            'totalDebit' => round((float) $totals->d, 2),
            'totalCredit' => round((float) $totals->c, 2),
        ]);
    }

    /** Buku Besar per akun dengan saldo awal dan saldo berjalan. */
    public function ledger(Request $request)
    {
        [$from, $to] = $this->period($request);
        $accounts = Account::orderBy('code')->get();
        $account = $accounts->firstWhere('id', (int) $request->account_id) ?? $accounts->first();

        $opening = $account->balance(\Carbon\Carbon::parse($from)->subDay()->toDateString());

        $lines = JournalLine::with('journal')
            ->where('account_id', $account->id)
            ->whereHas('journal', fn ($q) => $q->whereBetween('journal_date', [$from, $to]))
            ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
            ->orderBy('journals.journal_date')->orderBy('journals.id')->orderBy('journal_lines.id')
            ->select('journal_lines.*')
            ->get();

        $running = $opening;
        foreach ($lines as $line) {
            $delta = (float) $line->debit - (float) $line->credit;
            $running = round($running + ($account->normal_balance === 'debit' ? $delta : -$delta), 2);
            $line->running_balance = $running;
        }

        return view('reports.ledger', [
            'accounts' => $accounts,
            'account' => $account,
            'from' => $from,
            'to' => $to,
            'opening' => $opening,
            'lines' => $lines,
            'closing' => $running,
            'totalDebit' => round((float) $lines->sum('debit'), 2),
            'totalCredit' => round((float) $lines->sum('credit'), 2),
        ]);
    }

    public function trialBalance(Request $request)
    {
        $asOf = $request->date('as_of')?->toDateString() ?? today()->toDateString();

        return view('reports.trial-balance', ['asOf' => $asOf] + $this->reports->trialBalance($asOf));
    }

    public function incomeStatement(Request $request)
    {
        [$from, $to] = $this->period($request);

        return view('reports.income-statement', ['from' => $from, 'to' => $to] + $this->reports->incomeStatement($from, $to));
    }

    public function balanceSheet(Request $request)
    {
        $asOf = $request->date('as_of')?->toDateString() ?? today()->toDateString();

        return view('reports.balance-sheet', ['asOf' => $asOf] + $this->reports->balanceSheet($asOf));
    }

    /** @return array{0: string, 1: string} */
    private function period(Request $request): array
    {
        $from = $request->date('from')?->toDateString() ?? today()->startOfMonth()->toDateString();
        $to = $request->date('to')?->toDateString() ?? today()->toDateString();

        return $from > $to ? [$to, $from] : [$from, $to];
    }
}
