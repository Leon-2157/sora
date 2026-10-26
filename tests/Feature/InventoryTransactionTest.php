<?php

namespace Tests\Feature;

use App\Models\Category;
use App\Models\Product;
use App\Models\Purchase;
use App\Models\Sale;
use App\Models\Supplier;
use App\Models\User;
use App\Services\PurchaseService;
use App\Services\SaleService;
use Exception;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class InventoryTransactionTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;
    protected User $staff;
    protected Category $category;
    protected Supplier $supplier;
    protected Product $product;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->staff = User::factory()->create(['role' => 'staff']);
        $this->category = Category::create(['name' => 'Hardware']);
        $this->supplier = Supplier::create(['name' => 'Tech Supply Co.']);

        $this->product = Product::create([
            'category_id' => $this->category->id,
            'sku' => 'HW-001',
            'name' => 'USB Cable',
            'purchase_price' => 15000,
            'selling_price' => 25000,
            'stock' => 10,
            'minimum_stock' => 3,
        ]);
    }

    public function test_pending_purchase_does_not_alter_stock(): void
    {
        $service = app(PurchaseService::class);

        $purchase = $service->createPurchase([
            'supplier_id' => $this->supplier->id,
            'status' => 'pending',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 20,
                    'unit_cost' => 14000,
                ],
            ],
        ], $this->admin);

        $this->assertEquals('pending', $purchase->status);
        $this->assertEquals(10, $this->product->fresh()->stock);

        // Complete purchase -> stock increases to 30
        $service->completePurchase($purchase);
        $this->assertEquals(30, $this->product->fresh()->stock);
        $this->assertEquals('completed', $purchase->fresh()->status);
    }

    public function test_completed_sale_decrements_inventory_stock(): void
    {
        $service = app(SaleService::class);

        $sale = $service->createSale([
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 4,
                    'unit_price' => 25000,
                ],
            ],
        ], $this->staff);

        $this->assertEquals('completed', $sale->status);
        $this->assertEquals(6, $this->product->fresh()->stock);
    }

    public function test_sale_rejects_insufficient_stock(): void
    {
        $this->expectException(Exception::class);
        $this->expectExceptionMessage('insufficient');

        $service = app(SaleService::class);

        $service->createSale([
            'status' => 'completed',
            'items' => [
                [
                    'product_id' => $this->product->id,
                    'quantity' => 15, // Stock is only 10
                    'unit_price' => 25000,
                ],
            ],
        ], $this->staff);
    }

    public function test_category_cannot_be_deleted_when_products_exist(): void
    {
        $response = $this->actingAs($this->admin)->delete("/categories/{$this->category->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('categories', ['id' => $this->category->id, 'deleted_at' => null]);
    }

    public function test_user_cannot_delete_themselves(): void
    {
        $response = $this->actingAs($this->admin)->delete("/users/{$this->admin->id}");

        $response->assertSessionHas('error');
        $this->assertDatabaseHas('users', ['id' => $this->admin->id, 'deleted_at' => null]);
    }
}
