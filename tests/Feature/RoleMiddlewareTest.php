<?php

namespace Tests\Feature;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RoleMiddlewareTest extends TestCase
{
    use RefreshDatabase;

    public function test_admin_can_access_restricted_administration_routes(): void
    {
        $admin = User::factory()->create([
            'role' => 'admin',
        ]);

        $this->actingAs($admin)->get('/categories')->assertStatus(200);
        $this->actingAs($admin)->get('/suppliers')->assertStatus(200);
        $this->actingAs($admin)->get('/users')->assertStatus(200);
        $this->actingAs($admin)->get('/products/create')->assertStatus(200);
    }

    public function test_staff_is_forbidden_from_administrative_routes(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $this->actingAs($staff)->get('/categories')->assertStatus(403);
        $this->actingAs($staff)->get('/suppliers')->assertStatus(403);
        $this->actingAs($staff)->get('/users')->assertStatus(403);
        $this->actingAs($staff)->get('/products/create')->assertStatus(403);
    }

    public function test_staff_can_access_operational_terminal_and_catalogs(): void
    {
        $staff = User::factory()->create([
            'role' => 'staff',
        ]);

        $this->actingAs($staff)->get('/dashboard')->assertStatus(200);
        $this->actingAs($staff)->get('/sales/pos')->assertStatus(200);
        $this->actingAs($staff)->get('/products')->assertStatus(200);
        $this->actingAs($staff)->get('/products/low-stock')->assertStatus(200);
        $this->actingAs($staff)->get('/sales')->assertStatus(200);
        $this->actingAs($staff)->get('/purchases')->assertStatus(200);
    }
}
