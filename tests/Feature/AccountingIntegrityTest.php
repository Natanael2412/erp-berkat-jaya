<?php

namespace Tests\Feature;

use App\Models\Account;
use App\Models\Journal;
use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use App\Models\Supplier;
use App\Models\Unit;
use App\Models\User;
use App\Services\ProductService;
use App\Services\PurchaseService;
use App\Services\ReportService;
use App\Services\SalesService;
use App\Support\Accounts;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Validation\ValidationException;
use Tests\TestCase;

/**
 * Bukti integritas akuntansi untuk sidang:
 * - setiap jurnal seimbang, neraca saldo & neraca seimbang,
 * - saldo akun Persediaan di buku besar = nilai stok di kartu stok,
 * - HPP moving average & konversi satuan dihitung benar.
 */
class AccountingIntegrityTest extends TestCase
{
    use RefreshDatabase;

    protected bool $seed = true;

    private function service(string $class)
    {
        return app($class);
    }

    private function bal(string $code): float
    {
        return Account::where('code', $code)->first()->balance();
    }

    public function test_demo_scenario_keeps_books_balanced(): void
    {
        $this->seed(DemoSeeder::class);

        foreach (Journal::with('lines')->get() as $j) {
            $this->assertEqualsWithDelta($j->totalDebit(), $j->totalCredit(), 0.001, "Jurnal {$j->journal_number} tidak seimbang");
        }

        $reports = $this->service(ReportService::class);
        $tb = $reports->trialBalance(today()->toDateString());
        $this->assertTrue($tb['balanced'], 'Neraca saldo tidak seimbang');

        $bs = $reports->balanceSheet(today()->toDateString());
        $this->assertTrue($bs['balanced'], 'Neraca (Aset = Liabilitas + Ekuitas) tidak seimbang');

        $stockValue = Product::all()->sum(fn ($p) => $p->stockValue());
        $this->assertEqualsWithDelta($stockValue, $this->bal(Accounts::PERSEDIAAN), 1.0, 'Persediaan buku besar != kartu stok');

        foreach (Product::all() as $p) {
            $last = StockMovement::where('product_id', $p->id)->latest('id')->first();
            if ($last) {
                $this->assertEqualsWithDelta((float) $p->current_stock, (float) $last->balance_qty, 0.0001);
            }
        }
    }

    public function test_moving_average_and_unit_conversion(): void
    {
        $owner = User::where('role', 'owner')->first();
        $this->actingAs($owner);
        $u = Unit::pluck('id', 'code');

        $product = $this->service(ProductService::class)->create([
            'sku' => 'T-1', 'name' => 'Kabel Uji', 'base_unit_id' => $u['M'], 'base_selling_price' => 7000,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 320000]],
            'opening_qty' => 100, 'opening_cost' => 5000,
        ]);

        $roll = $product->units()->where('unit_id', $u['ROLL'])->first();
        $purchases = $this->service(PurchaseService::class);
        $po = $purchases->createOrder([
            'supplier_id' => Supplier::first()->id, 'order_date' => today()->toDateString(),
            'items' => [['product_unit_id' => $roll->id, 'qty_ordered' => 2, 'unit_price' => 300000]],
        ]);
        $item = $po->items()->first();

        // 2 roll x 50 m = 100 m @ Rp 6.000/m.
        $purchases->receive($po, today()->toDateString(), [$item->id => ['qty_received' => 2, 'qty_rejected' => 0]]);

        $product->refresh();
        $this->assertEqualsWithDelta(200, (float) $product->current_stock, 0.0001);
        // ((100 x 5000) + (100 x 6000)) / 200 = 5500
        $this->assertEqualsWithDelta(5500, (float) $product->current_hpp, 0.0001);
        $this->assertEquals('completed', $po->fresh()->status);
        $this->assertEqualsWithDelta(600000, $this->bal(Accounts::UTANG_USAHA), 0.01);

        // Jual 1 roll (50 m): HPP = 50 x 5500 = 275.000
        $this->actingAs(User::where('role', 'cashier')->first());
        $sale = $this->service(SalesService::class)->checkout([['product_unit_id' => $roll->id, 'qty' => 1]], 'cash', 400000);

        $this->assertEqualsWithDelta(320000, $sale->total(), 0.01);
        $this->assertEqualsWithDelta(80000, $sale->change(), 0.01);
        $this->assertEqualsWithDelta(150, (float) $product->fresh()->current_stock, 0.0001);
        $this->assertEqualsWithDelta(275000, $this->bal(Accounts::HPP), 0.01);
        $this->assertEqualsWithDelta(320000, $this->bal(Accounts::PENJUALAN), 0.01);
        $this->assertEqualsWithDelta(320000, $this->bal(Accounts::KAS), 0.01);
    }

    public function test_cannot_oversell_and_rejected_goods_do_not_enter_stock(): void
    {
        $this->actingAs(User::where('role', 'owner')->first());
        $u = Unit::pluck('id', 'code');

        $product = $this->service(ProductService::class)->create([
            'sku' => 'T-2', 'name' => 'Lampu Uji', 'base_unit_id' => $u['PCS'], 'base_selling_price' => 20000,
        ]);
        $pcs = $product->units()->first();

        $purchases = $this->service(PurchaseService::class);
        $po = $purchases->createOrder([
            'supplier_id' => Supplier::first()->id, 'order_date' => today()->toDateString(),
            'items' => [['product_unit_id' => $pcs->id, 'qty_ordered' => 10, 'unit_price' => 15000]],
        ]);
        $item = $po->items()->first();
        $purchases->receive($po, today()->toDateString(), [$item->id => ['qty_received' => 10, 'qty_rejected' => 3]]);

        $this->assertEqualsWithDelta(7, (float) $product->fresh()->current_stock, 0.0001);
        $this->assertEqualsWithDelta(105000, $this->bal(Accounts::UTANG_USAHA), 0.01);
        $this->assertEquals('partial', $po->fresh()->status, 'Barang ditolak masih menjadi sisa pesanan');

        // Tidak boleh menerima melebihi sisa pesanan (3).
        try {
            $purchases->receive($po, today()->toDateString(), [$item->id => ['qty_received' => 4, 'qty_rejected' => 0]]);
            $this->fail('Seharusnya ditolak karena melebihi sisa pesanan');
        } catch (ValidationException) {
        }

        $this->expectException(ValidationException::class);
        $this->service(SalesService::class)->checkout([['product_unit_id' => $pcs->id, 'qty' => 8]], 'cash', 1000000);
    }

    public function test_journals_are_immutable(): void
    {
        $this->seed(DemoSeeder::class);
        $this->expectException(\LogicException::class);
        Journal::first()->update(['description' => 'diubah']);
    }

    public function test_cashier_is_redirected_to_pos_and_blocked_from_reports(): void
    {
        $response = $this->post('/login', ['email' => 'kasir@toko.com', 'password' => 'password']);
        $response->assertRedirect(route('pos.index'));

        $this->get(route('reports.journals'))->assertRedirect(route('pos.index'));
    }

    public function test_owner_pages_render(): void
    {
        $this->seed(DemoSeeder::class);
        $this->actingAs(User::where('role', 'owner')->first());
        $po = PurchaseOrder::first();
        $product = Product::first();
        $sale = \App\Models\Sale::first();

        foreach ([
            route('dashboard'), route('products.index'), route('products.create'), route('products.show', $product),
            route('products.edit', $product), route('units.index'), route('suppliers.index'), route('suppliers.create'),
            route('purchase-orders.index'), route('purchase-orders.create'), route('purchase-orders.show', $po),
            route('purchase-orders.return.form', $po), route('sales.index'), route('sales.show', $sale),
            route('cash.index'), route('reports.journals'), route('reports.ledger'), route('reports.trial-balance'),
            route('reports.income-statement'), route('reports.balance-sheet'), route('users.index'), route('users.create'),
            route('pos.index'), route('pos.receipt', $sale), route('profile.edit'),
        ] as $url) {
            $this->get($url)->assertOk();
        }
    }
}
