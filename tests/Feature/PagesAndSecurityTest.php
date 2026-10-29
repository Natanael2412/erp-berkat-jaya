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
        $response->assertSee('POS · Berkat Jaya');
        $response->assertSee('default_price');
        $response->assertSee('allows_decimal');
    }

    public function test_pos_allows_decimal_for_meter_and_rejects_for_pcs(): void
    {
        $this->seed(DemoSeeder::class);
        $cashier = User::where('role', 'cashier')->first();
        $this->actingAs($cashier);

        $kabel = Product::where('sku', 'KBL-NYM-2X15')->first();
        $meterUnit = $kabel->units()->where('unit_id', Unit::where('code', 'M')->value('id'))->first();

        $lampu = Product::where('sku', 'LMP-LED-12W')->first();
        $pcsUnit = $lampu->units()->where('unit_id', Unit::where('code', 'PCS')->value('id'))->first();

        // 1. Kabel dengan satuan Meter boleh desimal (e.g. 2.5 meter)
        $resMeter = $this->postJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'cart' => [
                ['product_unit_id' => $meterUnit->id, 'qty' => 2.5],
            ],
        ]);
        $resMeter->assertOk();
        $this->assertDatabaseHas('sales', ['payment_method' => 'cash']);

        // 2. Lampu dengan satuan Pcs TIDAK boleh desimal (e.g. 1.5 Pcs)
        $resPcs = $this->postJson(route('pos.checkout'), [
            'payment_method' => 'cash',
            'amount_paid' => 100000,
            'cart' => [
                ['product_unit_id' => $pcsUnit->id, 'qty' => 1.5],
            ],
        ]);
        $resPcs->assertStatus(422);
        $resPcs->assertJsonFragment([
            'message' => "Satuan Pcs ({$lampu->name}) tidak boleh berupa pecahan/desimal. Harus bilangan bulat.",
        ]);
    }
}
