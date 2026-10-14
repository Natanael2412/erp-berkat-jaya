<?php

namespace App\Support;

use Carbon\CarbonInterface;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\DB;

/**
 * Penomoran dokumen berurutan per bulan, contoh: PO-202610-0001.
 * Baris sequence di-lock (SELECT ... FOR UPDATE) sehingga dua transaksi bersamaan
 * tidak pernah mendapat nomor yang sama. WAJIB dipanggil di dalam DB::transaction().
 */
final class DocumentNumber
{
    public static function next(string $prefix, CarbonInterface|string|null $date = null): string
    {
        if (DB::transactionLevel() === 0) {
            throw new \LogicException('DocumentNumber::next() harus dipanggil di dalam transaksi database.');
        }

        $period = ($date ? Carbon::parse($date) : now())->format('Ym');

        DB::table('document_sequences')->insertOrIgnore([
            'prefix' => $prefix,
            'period' => $period,
            'last_number' => 0,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $row = DB::table('document_sequences')
            ->where('prefix', $prefix)
            ->where('period', $period)
            ->lockForUpdate()
            ->first();

        $next = $row->last_number + 1;

        DB::table('document_sequences')->where('id', $row->id)->update([
            'last_number' => $next,
            'updated_at' => now(),
        ]);

        return sprintf('%s-%s-%04d', $prefix, $period, $next);
    }
}
