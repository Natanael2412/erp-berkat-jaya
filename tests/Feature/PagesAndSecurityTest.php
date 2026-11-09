<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\PurchaseOrder;
use App\Models\Unit;
use App\Models\User;
use Database\Seeders\DemoSeeder;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PagesAndSecurityTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
        $this->seed(); // DatabaseSeeder
    }

    public function test_landing_and_login_pages_render_with_brand(): void
    {
        $this->get('/')
            ->assertOk()
            ->assertSee('Berkat Jaya')
            ->assertSee('Masuk Sistem')
            ->assertSee('0x2e708bb91ad3ac63:0x758c36b20c854421');
        $this->get('/login')->assertOk()->assertSee('Masuk ke Sistem');
    }

    public function test_security_headers_are_present(): void
    {
        $this->get('/login')
            ->assertHeader('X-Frame-Options', 'SAMEORIGIN')
            ->assertHeader('X-Content-Type-Options', 'nosniff');
    }

    public function test_receive_form_renders_and_product_can_be_created(): void
    {
        $this->seed(DemoSeeder::class);
        $this->actingAs(User::where('role', 'owner')->first());

        $po = PurchaseOrder::whereIn('status', ['open', 'partial'])->first();
        if ($po) {
            $this->get(route('purchase-orders.receive.form', $po))->assertOk();
        }

        $pcs = Unit::where('code', 'PCS')->first();
        $this->post(route('products.store'), [
            'sku' => 'TST-001', 'name' => 'Produk Uji', 'base_unit_id' => $pcs->id,
            'min_stock' => 2, 'base_selling_price' => 10000,
        ])->assertRedirect();

        $this->assertNotNull(Product::where('sku', 'TST-001')->first());
    }

    public function test_deactivated_user_is_kicked_from_every_page(): void
    {
        $user = User::where('role', 'cashier')->first();
        $this->actingAs($user);
        $user->update(['is_active' => false]);

        $this->get(route('profile.edit'))->assertRedirect(route('login'));
        $this->assertGuest();
    }

    public function test_seeder_does_not_reset_existing_passwords(): void
    {
        $owner = User::where('role', 'owner')->first();
        $owner->update(['password' => 'rahasia-baru-123']);

        $this->seed();

        $this->assertTrue(\Illuminate\Support\Facades\Hash::check('rahasia-baru-123', $owner->fresh()->password));
    }

    public function test_pos_page_renders_with_products_and_units(): void
    {
        $this->seed(DemoSeeder::class);
        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($cashier);

        $response = $this->get(route('pos.index'));
        $response->assertOk();
        $response->assertSee('Point of Sales (POS) · Berkat Jaya');
        $response->assertSee('default_price');
        $response->assertSee('batches');
    }

    public function test_pos_enforces_integer_quantities_and_prevents_cross_roll_cutting(): void
    {
        $this->seed(DemoSeeder::class);
        $owner = User::where('role', 'owner')->first();
        $this->actingAs($owner);

        $u = Unit::pluck('id', 'code');
        $kabel = app(\App\Services\ProductService::class)->create([
            'sku' => 'KBL-ROL-TEST',
            'name' => 'Kabel Tes Rol Fisik',
            'base_unit_id' => $u['M'],
            'min_stock' => 10,
            'base_selling_price' => 10000,
            'units' => [['unit_id' => $u['ROLL'], 'conversion_factor' => 50, 'selling_price' => 450000]],
        ]);
        $rollUnit = $kabel->units()->where('unit_id', $u['ROLL'])->first();
        $meterUnit = $kabel->units()->where('unit_id', $u['M'])->first();

        // Buat PO 3 Rol @ 50m dan terima penuh (Inbound PO Komplit)
        $purchases = app(\App\Services\PurchaseService::class);
        $po = $purchases->createOrder([
            'supplier_id' => \App\Models\Supplier::first()->id,
            'order_date' => today()->toDateString(),
            'items' => [['product_unit_id' => $rollUnit->id, 'qty_ordered' => 3, 'unit_price' => 350000]],
        ]);
        $poItem = $po->items()->first();

        $this->post(route('purchase-orders.receive', $po), [
            'receipt_date' => today()->toDateString(),
            'items' => [
                $poItem->id => ['qty_received' => 3, 'qty_rejected' => 0],
            ],
        ])->assertRedirect(route('purchase-orders.show', $po));

        // Pastikan 3 Rol men-generate 3 baris SKU/Batch terpisah masing-masing 50 meter
        $batches = \App\Models\ProductBatch::where('product_id', $kabel->id)->orderBy('id')->get();
        $this->assertCount(3, $batches);
        foreach ($batches as $b) {
            $this->assertEquals(50, $b->initial_qty);
            $this->assertEquals(50, $b->remaining_qty);
        }

        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($cashier);

        // 1. Kuantitas pecahan/desimal harus ditolak (bahkan untuk satuan Meter)
        $resDecimal = $this->postJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'cart' => [
                ['product_unit_id' => $meterUnit->id, 'product_batch_id' => $batches[0]->id, 'qty' => 2.5],
            ],
        ]);
        $resDecimal->assertStatus(422);

        // 2. Potong 25m dari Rol #1 dan 25m dari Rol #2, serta habiskan 50m dari Rol #3
        $this->postJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 2000000,
            'cart' => [
                ['product_unit_id' => $meterUnit->id, 'product_batch_id' => $batches[0]->id, 'qty' => 25],
                ['product_unit_id' => $meterUnit->id, 'product_batch_id' => $batches[1]->id, 'qty' => 25],
                ['product_unit_id' => $meterUnit->id, 'product_batch_id' => $batches[2]->id, 'qty' => 50],
            ],
        ])->assertOk();

        $this->assertEquals(25, $batches[0]->fresh()->remaining_qty);
        $this->assertEquals(25, $batches[1]->fresh()->remaining_qty);
        $this->assertEquals(0, $batches[2]->fresh()->remaining_qty);
        $this->assertEquals(50, (int) $kabel->fresh()->current_stock);

        // 3. Pelanggan ingin membeli 50m utuh padahal stok fisik tersisa 2 rol @ 25m -> HARUS DITOLAK!
        $resCrossRoll = $this->postJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 1000000,
            'cart' => [
                ['product_unit_id' => $meterUnit->id, 'product_batch_id' => $batches[0]->id, 'qty' => 50],
            ],
        ]);
        $resCrossRoll->assertStatus(422);
    }
}
