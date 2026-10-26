<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Product;
use App\Models\Supplier;
use Illuminate\Database\Seeder;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $catElectronics = Category::firstOrCreate(['name' => 'Electronics']);
        $catStationery = Category::firstOrCreate(['name' => 'Office Stationery']);
        $catBeverages = Category::firstOrCreate(['name' => 'Beverages']);
        $catFood = Category::firstOrCreate(['name' => 'Food & Snacks']);
        $catCare = Category::firstOrCreate(['name' => 'Personal Care']);

        Supplier::firstOrCreate(
            ['name' => 'PT Sumber Makmur'],
            ['phone' => '081234567890', 'email' => 'sales@sumbermakmur.co.id']
        );
        Supplier::firstOrCreate(
            ['name' => 'CV Abadi Jaya'],
            ['phone' => '082198765432', 'email' => 'contact@abadijaya.com']
        );
        Supplier::firstOrCreate(
            ['name' => 'PT Maju Bersama'],
            ['phone' => '085712348765', 'email' => 'order@majubersama.id']
        );

        $demoProducts = [
            [
                'category_id' => $catElectronics->id,
                'sku' => 'EL-MOU-001',
                'name' => 'Wireless Optical Mouse',
                'purchase_price' => 85000,
                'selling_price' => 125000,
                'stock' => 24,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $catElectronics->id,
                'sku' => 'EL-KEY-002',
                'name' => 'Mechanical Keyboard TKL',
                'purchase_price' => 310000,
                'selling_price' => 450000,
                'stock' => 12,
                'minimum_stock' => 3,
            ],
            [
                'category_id' => $catStationery->id,
                'sku' => 'ST-PEN-001',
                'name' => 'Gel Pen 0.5mm Black',
                'purchase_price' => 3500,
                'selling_price' => 6500,
                'stock' => 150,
                'minimum_stock' => 20,
            ],
            [
                'category_id' => $catStationery->id,
                'sku' => 'ST-NBK-002',
                'name' => 'Hardcover Notebook A5',
                'purchase_price' => 22000,
                'selling_price' => 38000,
                'stock' => 4,
                'minimum_stock' => 10,
            ],
            [
                'category_id' => $catBeverages->id,
                'sku' => 'BV-COF-001',
                'name' => 'Arabica Coffee Beans 250g',
                'purchase_price' => 55000,
                'selling_price' => 85000,
                'stock' => 35,
                'minimum_stock' => 8,
            ],
            [
                'category_id' => $catBeverages->id,
                'sku' => 'BV-MAT-002',
                'name' => 'Matcha Green Tea Powder 100g',
                'purchase_price' => 42000,
                'selling_price' => 65000,
                'stock' => 2,
                'minimum_stock' => 5,
            ],
            [
                'category_id' => $catFood->id,
                'sku' => 'FD-CHO-001',
                'name' => 'Dark Chocolate Artisan 80g',
                'purchase_price' => 19000,
                'selling_price' => 32000,
                'stock' => 60,
                'minimum_stock' => 15,
            ],
            [
                'category_id' => $catCare->id,
                'sku' => 'PC-SOAP-001',
                'name' => 'Organic Botanical Hand Soap 300ml',
                'purchase_price' => 28000,
                'selling_price' => 45000,
                'stock' => 28,
                'minimum_stock' => 5,
            ],
        ];

        foreach ($demoProducts as $prod) {
            Product::updateOrCreate(['sku' => $prod['sku']], $prod);
        }
    }
}
