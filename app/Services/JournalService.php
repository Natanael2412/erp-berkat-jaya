<?php

namespace App\Services;

use App\Models\Account;
use App\Models\Journal;
use App\Support\DocumentNumber;
use Carbon\CarbonInterface;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Facades\DB;

/**
 * Satu-satunya pintu untuk membuat jurnal.
 *
 * Aturan yang ditegakkan:
 * - Minimal 2 baris, setiap baris hanya debit ATAU kredit, tidak boleh negatif.
 * - Total debit = total kredit (dibulatkan 2 desimal) dan > 0.
 * - Jurnal tidak bisa diubah/dihapus; koreksi melalui reverse().
 */
class JournalService
{
    /** @var array<string, int> */
    private array $accountIds = [];

    /**
     * @param  array<int, array{account: string, debit?: float|int|string, credit?: float|int|string, memo?: string|null}>  $lines
     */
    public function post(
        CarbonInterface|string $date,
        string $description,
        array $lines,
        ?Model $source = null,
        ?Journal $reversalOf = null,
    ): Journal {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('Jurnal harus diposting di dalam transaksi database.');
        }

        $normalized = [];
        foreach ($lines as $line) {
            $debit = round((float) ($line['debit'] ?? 0), 2);
            $credit = round((float) ($line['credit'] ?? 0), 2);

            if ($debit < 0 || $credit < 0) {
                throw new \DomainException('Nilai debit/kredit tidak boleh negatif.');
            }
            if ($debit > 0 && $credit > 0) {
                throw new \DomainException('Satu baris jurnal hanya boleh berisi debit atau kredit.');
            }
            if ($debit == 0 && $credit == 0) {
                continue;
            }

            $normalized[] = [
                'account_id' => $this->accountId($line['account']),
                'debit' => $debit,
                'credit' => $credit,
                'memo' => $line['memo'] ?? null,
            ];
        }

        $totalDebit = round(array_sum(array_column($normalized, 'debit')), 2);
        $totalCredit = round(array_sum(array_column($normalized, 'credit')), 2);

        if (count($normalized) < 2) {
            throw new \DomainException('Jurnal minimal terdiri dari 2 baris.');
        }
        if (abs($totalDebit - $totalCredit) >= 0.005) {
            throw new \DomainException(sprintf(
                'Jurnal tidak seimbang: debit %s, kredit %s.',
                number_format($totalDebit, 2, ',', '.'),
                number_format($totalCredit, 2, ',', '.'),
            ));
        }

        $journal = Journal::create([
            'journal_number' => DocumentNumber::next('JU', $date),
            'journal_date' => $date,
            'description' => $description,
            'source_type' => $source?->getMorphClass(),
            'source_id' => $source?->getKey(),
            'reversal_of_id' => $reversalOf?->id,
            'created_by' => auth()->id(),
        ]);

        $journal->lines()->createMany($normalized);

        return $journal;
    }

    /** Jurnal pembalik: menukar debit dan kredit dari jurnal asal. */
    public function reverse(Journal $original, CarbonInterface|string $date, string $description, ?Model $source = null): Journal
    {
        if ($original->reversedBy()->exists()) {
            throw new \DomainException("Jurnal {$original->journal_number} sudah pernah dibalik.");
        }

        $lines = $original->lines()->with('account')->get()->map(fn ($l) => [
            'account' => $l->account->code,
            'debit' => $l->credit,
            'credit' => $l->debit,
            'memo' => 'Pembalik: ' . ($l->memo ?? $original->description),
        ])->all();

        return $this->post($date, $description, $lines, $source ?? $original->source, $original);
    }

    private function accountId(string $code): int
    {
        if (! isset($this->accountIds[$code])) {
            $id = Account::where('code', $code)->value('id');
            if (! $id) {
                throw new \DomainException("Akun {$code} tidak ditemukan di Bagan Akun. Jalankan seeder.");
            }
            $this->accountIds[$code] = $id;
        }

        return $this->accountIds[$code];
    }
}
