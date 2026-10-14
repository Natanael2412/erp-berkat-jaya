<?php

namespace App\Http\Controllers;

use App\Models\Sale;
use App\Services\SalesService;
use Illuminate\Http\Request;

class SaleController extends Controller
{
    public function __construct(private SalesService $service) {}

    public function index(Request $request)
    {
        $from = $request->date('from') ?? today()->startOfMonth();
        $to = $request->date('to') ?? today();

        $query = Sale::withTotal()->with('cashier')
            ->whereDate('sale_date', '>=', $from)
            ->whereDate('sale_date', '<=', $to)
            ->when($request->q, fn ($q, $s) => $q->where('invoice_number', 'like', "%{$s}%"))
            ->when($request->status, fn ($q, $s) => $q->where('status', $s));

        $sales = (clone $query)->latest('sale_date')->paginate(25)->withQueryString();
        $summary = (clone $query)->where('status', 'completed')->get();

        return view('sales.index', [
            'sales' => $sales,
            'from' => $from,
            'to' => $to,
            'summaryCount' => $summary->count(),
            'summaryTotal' => $summary->sum(fn ($s) => $s->total()),
        ]);
    }

    public function show(Sale $sale)
    {
        $sale->load(['items.productUnit.product', 'items.productUnit.unit', 'cashier', 'voider', 'journals.lines.account']);

        return view('sales.show', compact('sale'));
    }

    public function invoice(Sale $sale)
    {
        $sale->load(['items.productUnit.product', 'items.productUnit.unit', 'cashier']);

        return view('sales.invoice', compact('sale'));
    }

    public function void(Request $request, Sale $sale)
    {
        $data = $request->validate(['void_reason' => ['required', 'string', 'max:255']]);

        $this->service->void($sale, $data['void_reason']);

        return back()->with('success', "Transaksi {$sale->invoice_number} dibatalkan. Stok dikembalikan dan jurnal pembalik dibuat.");
    }
}
