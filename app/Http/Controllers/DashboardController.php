<?php

namespace App\Http\Controllers;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Sale;
use App\Services\ReportService;
use App\Support\Accounts;
use Illuminate\Support\Facades\DB;

class DashboardController extends Controller
{
    public function __construct(private ReportService $reports) {}

    public function index()
    {
        $user = auth()->user();
        $today = today()->toDateString();

        $data = [
            'lowStock' => Product::with('baseUnit')->where('is_active', true)
                ->whereColumn('current_stock', '<=', 'min_stock')->orderBy('current_stock')->limit(8)->get(),
            'openOrders' => PurchaseOrder::with('supplier')->whereIn('status', ['open', 'partial'])->latest('id')->limit(6)->get(),
        ];

        if ($user->isOwner()) {
            $totals = $this->reports->accountTotals(null, $today)->keyBy(fn ($r) => $r->account->code);
            $bal = fn ($code) => $totals[$code]->balance ?? 0.0;

            $todayIs = $this->reports->incomeStatement($today, $today);
            $monthIs = $this->reports->incomeStatement(today()->startOfMonth()->toDateString(), $today);

            // Grafik penjualan 7 hari dari jurnal akun Penjualan.
            $chart = collect(range(6, 0))->map(function ($i) {
                $d = today()->subDays($i);

                return ['label' => $d->translatedFormat('D d/m'), 'date' => $d->toDateString(), 'value' => 0.0];
            })->keyBy('date');

            DB::table('journal_lines')
                ->join('journals', 'journals.id', '=', 'journal_lines.journal_id')
                ->join('accounts', 'accounts.id', '=', 'journal_lines.account_id')
                ->where('accounts.code', Accounts::PENJUALAN)
                ->where('journals.journal_date', '>=', today()->subDays(6)->toDateString())
                ->groupBy('journals.journal_date')
                ->selectRaw('journals.journal_date AS d, SUM(credit) - SUM(debit) AS v')
                ->get()
                ->each(function ($row) use ($chart) {
                    $key = substr((string) $row->d, 0, 10);
                    if ($chart->has($key)) {
                        $chart->put($key, array_merge($chart[$key], ['value' => round((float) $row->v, 2)]));
                    }
                });

            $data += [
                'cash' => $bal(Accounts::KAS),
                'bank' => $bal(Accounts::BANK),
                'inventory' => $bal(Accounts::PERSEDIAAN),
                'payable' => $bal(Accounts::UTANG_USAHA),
                'grossProfitAllTime' => round(($bal(Accounts::PENJUALAN) - $bal(Accounts::RETUR_PENJUALAN)) - $bal(Accounts::HPP), 2),
                'today' => $todayIs,
                'month' => $monthIs,
                'chart' => $chart->values(),
                'recentSales' => Sale::withTotal()->with('cashier')->latest('sale_date')->limit(6)->get(),
                'stockValueCheck' => round((float) Product::selectRaw('COALESCE(SUM(current_stock * current_hpp),0) v')->value('v'), 2),
            ];
        }

        return view('dashboard', $data);
    }
}
