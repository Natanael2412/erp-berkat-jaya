<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Support\Accounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DatabaseSeeder extends Seeder
{
    /**
     * Data wajib: user per role, Bagan Akun (SAK EMKM), master satuan.
     * Data contoh (produk, saldo awal, transaksi): jalankan `php artisan db:seed --class=DemoSeeder`.
     */
    public function run(): void
    {
        // Produksi: password acak (atau SEED_DEFAULT_PASSWORD). Lokal: 'password' agar mudah dicoba.
        $password = env('SEED_DEFAULT_PASSWORD') ?: (app()->isProduction() ? Str::password(16, symbols: false) : 'password');
        $created = [];

        foreach ([
            ['Pemilik Toko', 'owner@toko.com', 'owner'],
            ['Admin Gudang', 'admin@toko.com', 'admin'],
            ['Kasir Toko', 'kasir@toko.com', 'cashier'],
        ] as [$name, $email, $role]) {
            $user = User::firstOrNew(['email' => $email]);
            $isNew = ! $user->exists;

            $user->forceFill([
                'name' => $name,
                'role' => $role,
                'is_active' => true,
                'email_verified_at' => $user->email_verified_at ?? now(),
            ]);
            // Jangan pernah menimpa password akun yang sudah ada saat seeder dijalankan ulang.
            if ($isNew) {
                $user->password = $password;
                $created[] = $email;
            }
            $user->save();
        }

        if ($created && app()->isProduction()) {
            $this->command?->warn('Akun awal dibuat: '.implode(', ', $created));
            $this->command?->warn("Password awal: {$password}  (segera ganti setelah login pertama)");
        }

        foreach (Accounts::chart() as [$code, $name, $type, $normal, $isCash]) {
            Account::updateOrCreate(['code' => $code], [
                'name' => $name,
                'type' => $type,
                'normal_balance' => $normal,
                'is_cash' => $isCash,
            ]);
        }

        foreach ([
            ['PCS', 'Pcs'], ['M', 'Meter'], ['ROLL', 'Roll'], ['BOX', 'Box'],
            ['DUS', 'Dus'], ['PAK', 'Pak'], ['LSN', 'Lusin'], ['UNIT', 'Unit'],
        ] as [$code, $name]) {
            Unit::updateOrCreate(['code' => $code], ['name' => $name]);
        }

        Supplier::updateOrCreate(['code' => 'SUP-001'], [
            'name' => 'PT Sinar Listrik Abadi',
            'phone' => '021-5550001',
            'address' => 'Glodok, Jakarta Barat',
        ]);
        Supplier::updateOrCreate(['code' => 'SUP-002'], [
            'name' => 'CV Kabel Nusantara',
            'phone' => '031-7770002',
            'address' => 'Surabaya',
        ]);
    }
}
