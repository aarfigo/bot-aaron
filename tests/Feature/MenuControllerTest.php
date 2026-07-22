<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;
use App\Models\User;
use App\Models\Menu;

class MenuControllerTest extends TestCase
{
    use RefreshDatabase;

    public function test_store_menu_validates_and_creates()
    {
        // create user
        $user = User::factory()->create(['role' => 'admin']);

        $this->actingAs($user)
            ->post(route('admin.menu.store'), ['menuName' => 'Prueba'])
            ->assertRedirect();

        $this->assertDatabaseHas('tbl_menu', ['menuName' => 'Prueba']);
    }
}
