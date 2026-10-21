<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ApiWorkflowTest extends TestCase
{
    use RefreshDatabase;

    private User $admin;

    private User $staff;

    private User $customer;

    private Category $category;

    private Supplier $supplier;

    private Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::create([
            'name' => 'Admin User',
            'email' => 'admin@test.com',
            'password' => 'password',
            'role' => 'admin',
            'is_active' => true,
        ]);

        $this->staff = User::create([
            'name' => 'Staff User',
            'email' => 'staff@test.com',
            'password' => 'password',
            'role' => 'staff',
            'is_active' => true,
        ]);

        $this->customer = User::create([
            'name' => 'Customer User',
            'email' => 'customer@test.com',
            'password' => 'password',
            'role' => 'customer',
            'is_active' => true,
        ]);

        $this->category = Category::create([
            'name' => 'Hardware',
            'description' => 'Perangkat keras komputer',
        ]);

        $this->supplier = Supplier::create([
            'name' => 'Supplier Utama',
            'contact_person' => 'Budi',
            'phone' => '0812345678',
            'email' => 'supplier@test.com',
            'address' => 'Jl. Test No. 1',
        ]);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'PRD-001',
            'name' => 'Flashdisk 64GB',
            'description' => 'USB 3.0 High Speed',
            'unit' => 'pcs',
            'selling_price' => 85000,
            'stock' => 0,
            'min_stock' => 5,
        ]);
    }

    public function test_authentication_workflow_for_all_roles(): void
    {
        // Login Admin
        $adminRes = $this->postJson('/api/login', [
            'email' => 'admin@test.com',
            'password' => 'password',
        ]);
        $adminRes->assertOk()
            ->assertJsonPath('status', 'success')
            ->assertJsonPath('data.user.role', 'admin');
        $adminToken = $adminRes->json('data.token');

        // Check profile / me endpoint
        $meRes = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->getJson('/api/me');
        $meRes->assertOk()
            ->assertJsonPath('data.user.email', 'admin@test.com');

        // Logout
        $logoutRes = $this->withHeader('Authorization', "Bearer {$adminToken}")
            ->postJson('/api/logout');
        $logoutRes->assertOk()
            ->assertJsonPath('status', 'success');

        // Login Staff
        $staffRes = $this->postJson('/api/login', [
            'email' => 'staff@test.com',
            'password' => 'password',
        ]);
        $staffRes->assertOk()
            ->assertJsonPath('data.user.role', 'staff');

        // Login Customer
        $custRes = $this->postJson('/api/login', [
            'email' => 'customer@test.com',
            'password' => 'password',
        ]);
        $custRes->assertOk()
            ->assertJsonPath('data.user.role', 'customer');
    }

    public function test_rbac_restrictions_for_customer(): void
    {
        $token = $this->customer->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        // Customer can read categories and products
        $this->withHeaders($headers)->getJson('/api/categories')->assertOk();
        $this->withHeaders($headers)->getJson('/api/products')->assertOk();

        // Customer cannot create category (403)
        $this->withHeaders($headers)->postJson('/api/categories', [
            'name' => 'Forbidden Cat',
        ])->assertForbidden();

        // Customer cannot access suppliers (403)
        $this->withHeaders($headers)->getJson('/api/suppliers')->assertForbidden();

        // Customer cannot access purchases or sales (403)
        $this->withHeaders($headers)->getJson('/api/purchases')->assertForbidden();
        $this->withHeaders($headers)->getJson('/api/sales')->assertForbidden();
    }

    public function test_category_crud_and_integrity_protection(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        // Create new category
        $createRes = $this->withHeaders($headers)->postJson('/api/categories', [
            'name' => 'Aksesoris Baru',
            'description' => 'Deskripsi',
        ]);
        $createRes->assertCreated()
            ->assertJsonPath('data.name', 'Aksesoris Baru');
        $catId = $createRes->json('data.id');

        // Show
        $this->withHeaders($headers)->getJson("/api/categories/{$catId}")->assertOk();

        // Update
        $this->withHeaders($headers)->putJson("/api/categories/{$catId}", [
            'name' => 'Aksesoris Updated',
        ])->assertOk()->assertJsonPath('data.name', 'Aksesoris Updated');

        // Delete empty category
        $this->withHeaders($headers)->deleteJson("/api/categories/{$catId}")->assertOk();

        // Integrity check: cannot delete category with products
        $this->withHeaders($headers)->deleteJson("/api/categories/{$this->category->id}")
            ->assertStatus(422);
    }

    public function test_supplier_crud_and_transaction_protection(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        $createRes = $this->withHeaders($headers)->postJson('/api/suppliers', [
            'name' => 'Supplier CV Baru',
            'contact_person' => 'Joko',
            'phone' => '081999888',
        ]);
        $createRes->assertCreated()
            ->assertJsonPath('data.name', 'Supplier CV Baru');
        $supplierId = $createRes->json('data.id');

        // Update
        $this->withHeaders($headers)->putJson("/api/suppliers/{$supplierId}", [
            'name' => 'Supplier CV Baru Perkasa',
        ])->assertOk();

        // Delete empty supplier
        $this->withHeaders($headers)->deleteJson("/api/suppliers/{$supplierId}")->assertOk();

        // Protection: supplier with transactions cannot be deleted
        Purchase::create([
            'number' => 'PO-TEST-001',
            'supplier_id' => $this->supplier->id,
            'user_id' => $this->admin->id,
            'status' => 'completed',
            'total' => 100000,
            'transacted_at' => now(),
        ]);
        $this->withHeaders($headers)->deleteJson("/api/suppliers/{$this->supplier->id}")
            ->assertStatus(422);
    }

    public function test_product_crud_filters_and_soft_delete(): void
    {
        $token = $this->admin->createToken('test')->plainTextToken;
        $headers = ['Authorization' => "Bearer {$token}"];

        // Create product
        $createRes = $this->withHeaders($headers)->postJson('/api/products', [
            'category_id' => $this->category->id,
            'sku' => 'PRD-002',
            'name' => 'Keyboard Wireless',
            'unit' => 'pcs',
            'selling_price' => 150000,
            'stock' => 2,
            'min_stock' => 5,
        ]);
        $createRes->assertCreated();
        $prodId = $createRes->json('data.id');

        // Filter low stock
        $lowStockRes = $this->withHeaders($headers)->getJson('/api/products?low_stock=true');
        $lowStockRes->assertOk();
        $this->assertGreaterThanOrEqual(1, count($lowStockRes->json('data.data')));

        // Soft delete
        $this->withHeaders($headers)->deleteJson("/api/products/{$prodId}")->assertOk();
        $this->assertSoftDeleted('products', ['id' => $prodId]);

        // Restore
        $this->withHeaders($headers)->postJson("/api/products/{$prodId}/restore")->assertOk();
        $this->assertNotSoftDeleted('products', ['id' => $prodId]);
    }

    public function test_stock_cycle_and_transaction_integrity(): void
    {
        $adminToken = $this->admin->createToken('test')->plainTextToken;
        $staffToken = $this->staff->createToken('test')->plainTextToken;
        $adminHeaders = ['Authorization' => "Bearer {$adminToken}"];
        $staffHeaders = ['Authorization' => "Bearer {$staffToken}"];

        // 1. Initial stock is 0
        $this->assertEquals(0, $this->product->fresh()->stock);

        // 2. Staff records Purchase of 10 units
        $purchaseRes = $this->withHeaders($staffHeaders)->postJson('/api/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 10,
                    'unit_cost' => 50000,
                ],
            ],
        ]);
        $purchaseRes->assertCreated();
        $purchaseId = $purchaseRes->json('data.id');
        $this->assertEquals(10, $this->product->fresh()->stock);

        // 3. Staff records Sale of 3 units
        $saleRes = $this->withHeaders($staffHeaders)->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 3,
                ],
            ],
        ]);
        $saleRes->assertCreated();
        $saleId = $saleRes->json('data.id');
        $this->assertEquals(7, $this->product->fresh()->stock);

        // 4. Sale exceeding stock (50 units) must fail with 422
        $excessSaleRes = $this->withHeaders($staffHeaders)->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 50,
                ],
            ],
        ]);
        $excessSaleRes->assertStatus(422)
            ->assertJsonPath('status', 'error');
        $this->assertEquals(7, $this->product->fresh()->stock);

        // 5. Admin cancels Sale of 3 units -> stock returns to 10
        app('auth')->forgetGuards();
        $cancelSaleRes = $this->withHeaders($adminHeaders)->postJson("/api/sales/{$saleId}/cancel", [
            'cancel_reason' => 'Customer batalkan pesanan',
        ]);
        $cancelSaleRes->assertOk();
        $this->assertEquals(10, $this->product->fresh()->stock);

        // 6. Admin cancels Purchase of 10 units -> stock returns to 0
        app('auth')->forgetGuards();
        $cancelPurchaseRes = $this->withHeaders($adminHeaders)->postJson("/api/purchases/{$purchaseId}/cancel", [
            'cancel_reason' => 'Barang retur ke supplier',
        ]);
        $cancelPurchaseRes->assertOk();
        $this->assertEquals(0, $this->product->fresh()->stock);

        // 7. Verify Purchase cancel rejection when physical stock is insufficient
        // Purchase 5 units -> stock becomes 5
        app('auth')->forgetGuards();
        $p2Res = $this->withHeaders($staffHeaders)->postJson('/api/purchases', [
            'supplier_id' => $this->supplier->id,
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 5,
                    'unit_cost' => 50000,
                ],
            ],
        ]);
        $p2Id = $p2Res->json('data.id');
        $this->assertEquals(5, $this->product->fresh()->stock);

        // Sell 4 units -> stock becomes 1
        $this->withHeaders($staffHeaders)->postJson('/api/sales', [
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                ],
            ],
        ])->assertCreated();
        $this->assertEquals(1, $this->product->fresh()->stock);

        // Attempting to cancel purchase of 5 when stock is only 1 must return 422
        app('auth')->forgetGuards();
        $p2CancelFail = $this->withHeaders($adminHeaders)->postJson("/api/purchases/{$p2Id}/cancel");
        $p2CancelFail->assertStatus(422)
            ->assertJsonPath('status', 'error');
        $this->assertEquals(1, $this->product->fresh()->stock);
    }
}
