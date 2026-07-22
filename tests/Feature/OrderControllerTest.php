<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;

class OrderControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_order_creates_rows()
    {
    // Use a role that is allowed to access staff routes (mesero/cocina_barra/admin)
    $user = User::factory()->create(['role' => 'mesero']);

        // Ensure there is a menu item to reference in the order
        \DB::table('tbl_menu')->insert(['menuID' => 100, 'menuName' => 'TestMenu']);
        \DB::table('tbl_menuitem')->insert(['itemID' => 17, 'menuID' => 100, 'menuItemName' => 'TestItem', 'price' => 10.00]);

        $this->actingAs($user)
            ->post(route('staff.orders.store'), [
                'items' => [
                    ['itemID' => 17, 'quantity' => 1]
                ]
            ])
            ->assertRedirect();

    $this->assertDatabaseHas('tbl_order', ['status' => 'waiting']);
    }
}
