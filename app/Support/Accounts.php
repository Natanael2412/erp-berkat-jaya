<?php

namespace App\Support;

/**
 * Kode akun sistem (Bagan Akun berbasis SAK EMKM).
 * Jurnal otomatis HANYA boleh memakai konstanta ini, bukan string literal.
 */
final class Accounts
{
    public const KAS = '1-1100';
    public const BANK = '1-1200';
    public const PERSEDIAAN = '1-1300';
    public const UTANG_USAHA = '2-1100';
    public const MODAL = '3-1100';
    public const PRIVE = '3-1200';
    public const LABA_DITAHAN = '3-1300';
    public const PENJUALAN = '4-1100';
    public const RETUR_PENJUALAN = '4-1200';
    public const HPP = '5-1100';
    public const SELISIH_PERSEDIAAN = '5-1200';
    public const BEBAN_OPERASIONAL = '6-1100';

    /** Definisi lengkap untuk seeder: [kode, nama, tipe, saldo normal, akun kas?] */
    public static function chart(): array
    {
        return [
            [self::KAS, 'Kas', 'asset', 'debit', true],
            [self::BANK, 'Bank', 'asset', 'debit', true],
            [self::PERSEDIAAN, 'Persediaan Barang Dagang', 'asset', 'debit', false],
            [self::UTANG_USAHA, 'Utang Usaha', 'liability', 'credit', false],
            [self::MODAL, 'Modal Pemilik', 'equity', 'credit', false],
            [self::PRIVE, 'Prive', 'equity', 'debit', false],
            [self::LABA_DITAHAN, 'Laba Ditahan', 'equity', 'credit', false],
            [self::PENJUALAN, 'Penjualan', 'revenue', 'credit', false],
            [self::RETUR_PENJUALAN, 'Retur & Potongan Penjualan', 'revenue', 'debit', false],
            [self::HPP, 'Harga Pokok Penjualan', 'expense', 'debit', false],
            [self::SELISIH_PERSEDIAAN, 'Selisih Persediaan', 'expense', 'debit', false],
            [self::BEBAN_OPERASIONAL, 'Beban Operasional', 'expense', 'debit', false],
        ];
    }

    /** Akun kas/bank untuk metode pembayaran POS. */
    public static function forPaymentMethod(string $method): string
    {
        return $method === 'cash' ? self::KAS : self::BANK;
    }
}
