<?php

if (! function_exists('rp')) {
    /** Format Rupiah: rp(1500000) => "Rp 1.500.000". Negatif ditampilkan dalam kurung (gaya akuntansi). */
    function rp(float|int|string|null $value, bool $accounting = false): string
    {
        $value = round((float) $value, 2);
        $formatted = 'Rp ' . number_format(abs($value), 0, ',', '.');

        if ($value < 0) {
            return $accounting ? "({$formatted})" : "-{$formatted}";
        }

        return $formatted;
    }
}

if (! function_exists('num')) {
    /** Format angka tanpa desimal nol berlebih: num(12.5000) => "12,5". */
    function num(float|int|string|null $value, int $decimals = 4): string
    {
        $s = number_format((float) $value, $decimals, ',', '.');

        return str_contains($s, ',') ? rtrim(rtrim($s, '0'), ',') : $s;
    }
}
