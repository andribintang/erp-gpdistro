<?php

namespace Database\Seeders;

use App\Models\Brand;
use App\Models\Category;
use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\Supplier;
use App\Models\Warehouse;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class DemoDataSeeder extends Seeder
{
    public function run(): void
    {
        $warehouse = Warehouse::query()->firstOrCreate(
            ['code' => 'WH-JKT'],
            [
                'name' => 'Gudang Utama Jakarta',
                'city' => 'Jakarta',
                'address' => 'Gudang demo lokal GPDISTRO',
                'is_active' => true,
            ],
        );

        Warehouse::query()->firstOrCreate(
            ['code' => 'WH-SBY'],
            [
                'name' => 'Gudang Surabaya',
                'city' => 'Surabaya',
                'address' => 'Cabang timur',
                'is_active' => true,
            ],
        );

        Supplier::query()->firstOrCreate(
            ['name' => 'Supplier Demo Tekstil'],
            [
                'email' => 'supplier@example.test',
                'phone' => '081234567890',
                'address' => 'Jakarta',
            ],
        );

        Customer::query()->firstOrCreate(
            ['email' => 'pelanggan@demo.test'],
            [
                'name' => 'Pelanggan Demo Racing',
                'phone' => '081299988877',
                'address' => 'Jakarta Selatan',
            ],
        );

        $brand = Brand::query()->firstOrCreate(
            ['slug' => 'gpdistro-racing'],
            ['name' => 'GPDISTRO Racing'],
        );

        $categoryApparel = Category::query()->firstOrCreate(
            ['slug' => 'apparel-racing'],
            ['name' => 'Apparel Racing'],
        );

        $categorySpare = Category::query()->firstOrCreate(
            ['slug' => 'spare-parts'],
            ['name' => 'Spare Parts'],
        );

        $products = [
            [
                'sku' => 'JRS-DEMO-001',
                'name' => 'Jersey Racing Demo',
                'slug' => 'jersey-racing-demo',
                'product_type' => 'apparel',
                'brand_id' => $brand->id,
                'category_id' => $categoryApparel->id,
                'price' => 275000,
                'stock_on_hand' => 18,
                'minimum_stock' => 5,
            ],
            [
                'sku' => 'SPR-DEMO-001',
                'name' => 'Brake Lever Demo',
                'slug' => 'brake-lever-demo',
                'product_type' => 'spare_part',
                'brand_id' => $brand->id,
                'category_id' => $categorySpare->id,
                'price' => 185000,
                'stock_on_hand' => 4,
                'minimum_stock' => 6,
            ],
        ];

        foreach ($products as $data) {
            $product = Product::query()->updateOrCreate(
                ['sku' => $data['sku']],
                [
                    'name' => $data['name'],
                    'slug' => $data['slug'],
                    'product_type' => $data['product_type'],
                    'brand_id' => $data['brand_id'],
                    'category_id' => $data['category_id'],
                    'price' => $data['price'],
                    'is_active' => true,
                ],
            );

            $inventory = Inventory::query()->firstOrCreate(
                [
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                ],
                [
                    'stock_on_hand' => $data['stock_on_hand'],
                    'reserved_stock' => 0,
                    'minimum_stock' => $data['minimum_stock'],
                ],
            );

            if ($inventory->wasRecentlyCreated) {
                $inventory->movements()->create([
                    'product_id' => $product->id,
                    'warehouse_id' => $warehouse->id,
                    'type' => 'initial',
                    'quantity' => $data['stock_on_hand'],
                    'stock_before' => 0,
                    'stock_after' => $data['stock_on_hand'],
                    'notes' => 'Saldo awal data demo lokal.',
                ]);
            }
        }
    }
}
