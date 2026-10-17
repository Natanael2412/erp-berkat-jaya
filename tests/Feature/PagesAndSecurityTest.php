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
}
