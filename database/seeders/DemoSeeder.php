<?php

namespace Database\Seeders;

use App\Models\Account;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\CashService;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\SalesService;
use App\Support\Accounts;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Auth;

/**
 * Skenario demo end-to-end melalui service asli (bukan insert manual),
 * sehingga semua jurnal & kartu stok terbentuk persis seperti pemakaian nyata.
 */
class DemoSeeder extends Seeder
{
    public function run(ProductService $products, PurchaseService $purchases, SalesService $sales, CashService $cash): void
    {
        if (Product::exists()) {
            $this->command?->warn('Produk sudah ada, DemoSeeder dilewati.');

            return;
        }

        $owner = User::where('role', 'owner')->firstOrFail();
        Auth::login($owner);

        $u = Unit::pluck('id', 'code');
        $kas = Account::where('code', Accounts::KAS)->value('id');
        $bank = Account::where('code', Accounts::BANK)->value('id');
        $date = now()->startOfMonth()->toDateString();

        $cash->record(['type' => 'capital', 'cash_account_id' => $kas, 'amount' => 10_000_000, 'description' => 'Setoran modal awal', 'transaction_date' => $date]);
        $cash->record(['type' => 'capital', 'cash_account_id' => $bank, 'amount' => 15_000_000, 'description' => 'Setoran modal ke rekening toko', 'transaction_date' => $date]);

        $kabel = $products->create([
            'sku' => 'KBL-NYM-2X15', 'name' => 'Kabel NYM 2x1.5mm Eterna', 'base_unit_id' => $u['M'], 'min_stock' => 50,
            'base_selling_price' => 6500,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 300000]],
            'opening_qty' => 100, 'opening_cost' => 5000, 'opening_date' => $date,
        ]);
        $lampu = $products->create([
            'sku' => 'LMP-LED-12W', 'name' => 'Lampu LED Philips 12W', 'base_unit_id' => $u['PCS'], 'min_stock' => 12,
            'base_selling_price' => 45000,
            'units' => [['unit_id' => $u['DUS'], 'conversion_factor' => 12, 'selling_price' => 500000]],
            'opening_qty' => 24, 'opening_cost' => 35000, 'opening_date' => $date,
        ]);
        $saklar = $products->create([
            'sku' => 'SKL-BRC-01', 'name' => 'Saklar Tunggal Broco', 'base_unit_id' => $u['PCS'], 'min_stock' => 10,
            'base_selling_price' => 18000,
            'units' => [['unit_id' => $u['BOX'], 'conversion_factor' => 10, 'selling_price' => 165000]],
        ]);
        $mcb = $products->create([
            'sku' => 'MCB-SCH-6A', 'name' => 'MCB Schneider 6A', 'base_unit_id' => $u['PCS'], 'min_stock' => 5,
            'base_selling_price' => 65000,
            'opening_qty' => 10, 'opening_cost' => 48000, 'opening_date' => $date,
        ]);

        $roll = $kabel->units()->where('unit_id', $u['ROLL'])->first();
        $box = $saklar->units()->where('unit_id', $u['BOX'])->first();

        $po = $purchases->createOrder([
            'supplier_id' => Supplier::where('code', 'SUP-001')->value('id'),
            'order_date' => $date,
            'items' => [
                ['product_unit_id' => $roll->id, 'qty_ordered' => 4, 'unit_price' => 275000],
                ['product_unit_id' => $box->id, 'qty_ordered' => 5, 'unit_price' => 120000],
            ],
        ]);
        $items = $po->items()->get()->keyBy('product_unit_id');

        // 4 roll datang, 1 cacat ditolak; saklar baru datang 3 box.
        $purchases->receive($po, $date, [
            $items[$roll->id]->id => ['qty_received' => 4, 'qty_rejected' => 1],
            $items[$box->id]->id => ['qty_received' => 3, 'qty_rejected' => 0],
        ], 'Satu roll kabel terkelupas, ditolak.');
        // Sisa 2 box saklar menyusul.
        $purchases->receive($po, $date, [
            $items[$box->id]->id => ['qty_received' => 2, 'qty_rejected' => 0],
        ]);
        // Retur 1 box saklar setelah diterima (salah tipe).
        $purchases->returnGoods($po, $date, [$items[$box->id]->id => 1], 'Salah tipe, diganti potongan utang.');
        // Cicil utang.
        $purchases->pay($po->fresh(), $date, 500000, $bank, 'Cicilan pertama');

        $base = fn (Product $p) => $p->units()->where('unit_id', $p->base_unit_id)->value('id');

        Auth::login(User::where('role', 'cashier')->firstOrFail());
        $sales->checkout([
            ['product_unit_id' => $base($kabel), 'qty' => 15],
            ['product_unit_id' => $base($lampu), 'qty' => 4],
        ], 'cash', 300000);
        $sales->checkout([
            ['product_unit_id' => $roll->id, 'qty' => 1],
            ['product_unit_id' => $base($mcb), 'qty' => 2],
        ], 'qris', 0);
        $sale = $sales->checkout([
            ['product_unit_id' => $base($saklar), 'qty' => 3],
        ], 'cash', 100000);

        Auth::login($owner);
        $sales->void($sale, 'Salah input, pelanggan batal.');

        $cash->record(['type' => 'expense', 'cash_account_id' => $kas, 'amount' => 250000, 'description' => 'Listrik & air toko', 'transaction_date' => now()->toDateString()]);

        Auth::logout();
        $this->command?->info('Data demo berhasil dibuat.');
    }
}
