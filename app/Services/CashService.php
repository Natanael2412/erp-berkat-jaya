<?php

namespace App\Services;

use App\Models\Account;
use App\Models\CashTransaction;
use App\Support\Accounts;
use App\Support\DocumentNumber;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

/**
 * Transaksi kas di luar penjualan/pembelian:
 *  - Setoran modal : Kas/Bank (D) / Modal Pemilik (K)
 *  - Prive         : Prive (D)    / Kas/Bank (K)
 *  - Beban         : Beban Operasional (D) / Kas/Bank (K)
 */
class CashService
{
    public function __construct(private JournalService $journal) {}

    public function record(array $data): CashTransaction
    {
        return DB::transaction(function () use ($data) {
            $account = Account::whereKey($data['cash_account_id'])->where('is_cash', true)->lockForUpdate()->firstOrFail();
            $amount = round((float) $data['amount'], 2);

            if ($data['type'] !== 'capital') {
                $balance = $account->balance();
                if ($amount - $balance > 0.005) {
                    throw ValidationException::withMessages([
                        'amount' => "Saldo {$account->name} tidak cukup (Rp " . number_format($balance, 0, ',', '.') . ').',
                    ]);
                }
            }

            $trx = CashTransaction::create([
                'transaction_number' => DocumentNumber::next('KAS', $data['transaction_date']),
                'transaction_date' => $data['transaction_date'],
                'type' => $data['type'],
                'cash_account_id' => $account->id,
                'amount' => $amount,
                'description' => $data['description'],
                'created_by' => auth()->id(),
            ]);

            $lines = match ($data['type']) {
                'capital' => [
                    ['account' => $account->code, 'debit' => $amount],
                    ['account' => Accounts::MODAL, 'credit' => $amount],
                ],
                'drawing' => [
                    ['account' => Accounts::PRIVE, 'debit' => $amount],
                    ['account' => $account->code, 'credit' => $amount],
                ],
                'expense' => [
                    ['account' => Accounts::BEBAN_OPERASIONAL, 'debit' => $amount],
                    ['account' => $account->code, 'credit' => $amount],
                ],
            };

            $this->journal->post($data['transaction_date'], "{$trx->typeLabel()}: {$data['description']}", $lines, $trx);

            return $trx;
        });
    }
}
