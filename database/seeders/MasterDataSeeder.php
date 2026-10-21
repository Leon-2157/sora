<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\Supplier;
use App\Models\User;
use Illuminate\Database\Seeder;

class MasterDataSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // 1. Seed Users (Admin & Staff)
        User::firstOrCreate(
            ['email' => 'admin@sora.test'],
            [
                'name' => 'Administrator',
                'password' => 'admin123',
                'role' => 'admin',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'kasir@sora.test'],
            [
                'name' => 'Staff Kasir',
                'password' => 'kasir123',
                'role' => 'staff',
                'is_active' => true,
            ]
        );

        User::firstOrCreate(
            ['email' => 'customer@sora.test'],
            [
                'name' => 'Pelanggan Setia',
                'password' => 'customer123',
                'role' => 'customer',
                'is_active' => true,
            ]
        );

        // 2. Seed Suppliers
        $suppliers = [
            [
                'name' => 'PT Sumber Jaya Abadi',
                'contact_person' => 'Budi Santoso',
                'phone' => '081234567890',
                'email' => 'sales@sumberjaya.com',
                'address' => 'Jl. Industri Raya No. 45, Jakarta Utara',
            ],
            [
                'name' => 'CV Tech Global Mandiri',
                'contact_person' => 'Dewi Lestari',
                'phone' => '082198765432',
                'email' => 'contact@techglobal.id',
                'address' => 'Kawasan Pergudangan Surya No. 12, Bandung',
            ],
            [
                'name' => 'PT Mitra Elektronik Nusantara',
                'contact_person' => 'Hendro Wijaya',
                'phone' => '085612345678',
                'email' => 'info@mitraelektronik.co.id',
                'address' => 'Jl. Rungkut Industri III No. 8, Surabaya',
            ],
        ];

        foreach ($suppliers as $supplierData) {
            Supplier::firstOrCreate(['name' => $supplierData['name']], $supplierData);
        }

        // 3. Seed Categories & Products
        $categoriesWithProducts = [
            [
                'category' => [
                    'name' => 'Aksesoris Komputer',
                    'description' => 'Berbagai macam periferal dan aksesoris komputer/laptop',
                ],
                'products' => [
                    [
                        'sku' => 'ACC-001',
                        'name' => 'Mouse Wireless Silent Click',
                        'description' => 'Mouse tanpa kabel dengan fitur silent click hemat daya baterai',
                        'unit' => 'pcs',
                        'selling_price' => 125000,
                        'stock' => 35,
                        'min_stock' => 5,
                    ],
                    [
                        'sku' => 'ACC-002',
                        'name' => 'Keyboard Mechanical RGB 60%',
                        'description' => 'Keyboard mechanical ringkas dengan switch blue dan backlit RGB',
                        'unit' => 'pcs',
                        'selling_price' => 375000,
                        'stock' => 20,
                        'min_stock' => 5,
                    ],
                    [
                        'sku' => 'ACC-003',
                        'name' => 'Kabel HDMI 2.1 Braided 2 Meter',
                        'description' => 'Mendukung resolusi 4K 120Hz dan 8K 60Hz berlapis nylon braided',
                        'unit' => 'pcs',
                        'selling_price' => 75000,
                        'stock' => 50,
                        'min_stock' => 10,
                    ],
                ],
            ],
            [
                'category' => [
                    'name' => 'Komputer & Monitor',
                    'description' => 'Perangkat utama komputasi dan display visual',
                ],
                'products' => [
                    [
                        'sku' => 'CMP-001',
                        'name' => 'Monitor LED 24 Inch IPS 100Hz',
                        'description' => 'Panel IPS bezel-less dengan refresh rate 100Hz dan port HDMI/VGA',
                        'unit' => 'unit',
                        'selling_price' => 1350000,
                        'stock' => 12,
                        'min_stock' => 3,
                    ],
                    [
                        'sku' => 'CMP-002',
                        'name' => 'SSD NVMe M.2 512GB PCIe 4.0',
                        'description' => 'Kecepatan baca hingga 5000MB/s cocok untuk gaming dan produktivitas',
                        'unit' => 'pcs',
                        'selling_price' => 620000,
                        'stock' => 25,
                        'min_stock' => 5,
                    ],
                ],
            ],
            [
                'category' => [
                    'name' => 'Jaringan & Internet',
                    'description' => 'Peralatan pendukung koneksi jaringan lokal dan nirkabel',
                ],
                'products' => [
                    [
                        'sku' => 'NET-001',
                        'name' => 'Router Gigabit Wi-Fi 6 Dual Band',
                        'description' => 'Kecepatan hingga 1800Mbps dengan jangkauan sinyal luas dan 4 antena eksternal',
                        'unit' => 'unit',
                        'selling_price' => 480000,
                        'stock' => 15,
                        'min_stock' => 4,
                    ],
                    [
                        'sku' => 'NET-002',
                        'name' => 'Kabel LAN UTP Cat6 5 Meter',
                        'description' => 'Kabel jaringan high speed siap pakai terpasang konektor RJ45',
                        'unit' => 'pcs',
                        'selling_price' => 35000,
                        'stock' => 40,
                        'min_stock' => 10,
                    ],
                ],
            ],
            [
                'category' => [
                    'name' => 'Alat Tulis Kantor',
                    'description' => 'Kebutuhan perlengkapan dan operasional kantor',
                ],
                'products' => [
                    [
                        'sku' => 'ATK-001',
                        'name' => 'Kertas HVS A4 75 GSM (1 Rim)',
                        'description' => 'Kertas fotokopi putih berkualitas tinggi isi 500 lembar',
                        'unit' => 'rim',
                        'selling_price' => 55000,
                        'stock' => 60,
                        'min_stock' => 15,
                    ],
                ],
            ],
        ];

        foreach ($categoriesWithProducts as $item) {
            $category = Category::firstOrCreate(
                ['name' => $item['category']['name']],
                ['description' => $item['category']['description']]
            );

            foreach ($item['products'] as $productData) {
                $category->products()->firstOrCreate(
                    ['sku' => $productData['sku']],
                    $productData
                );
            }
        }
    }
}
