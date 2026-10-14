<?php

namespace App\Http\Controllers;

use App\Models\Account;
use App\Models\CashTransaction;
use App\Services\CashService;
use Illuminate\Http\Request;
use Illuminate\Validation\Rule;

class CashTransactionController extends Controller
{
    public function __construct(private CashService $service) {}

    public function index()
    {
        $cashAccounts = Account::where('is_cash', true)->orderBy('code')->get();

        return view('cash.index', [
            'cashAccounts' => $cashAccounts,
            'balances' => $cashAccounts->mapWithKeys(fn ($a) => [$a->id => $a->balance()]),
            'transactions' => CashTransaction::with(['cashAccount', 'creator'])->latest('id')->paginate(20),
        ]);
    }

    public function store(Request $request)
    {
        $data = $request->validate([
            'type' => ['required', Rule::in(array_keys(CashTransaction::TYPES))],
            'cash_account_id' => ['required', Rule::exists('accounts', 'id')->where('is_cash', true)],
            'amount' => ['required', 'numeric', 'gt:0'],
            'transaction_date' => ['required', 'date', 'before_or_equal:today'],
            'description' => ['required', 'string', 'max:255'],
        ]);

        $trx = $this->service->record($data);

        return back()->with('success', "{$trx->typeLabel()} {$trx->transaction_number} dicatat.");
    }
}
