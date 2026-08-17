<?php

namespace Tests\Feature\Admin;

use App\Models\Customer;
use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class OrderManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_customer_and_sales_order_with_stock_deduction(): void
    {
        Role::findOrCreate('Manager');
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $product = Product::query()->create([
            'sku' => 'SO-TEST-001',
            'name' => 'Helm Demo',
            'slug' => 'helm-demo-so-test',
            'product_type' => 'spare_part',
            'price' => 350000,
            'is_active' => true,
        ]);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH-SO',
            'name' => 'Gudang Penjualan',
            'is_active' => true,
        ]);
        Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_on_hand' => 10,
            'reserved_stock' => 0,
            'minimum_stock' => 2,
        ]);

        $this->actingAs($user)
            ->post(route('admin.customers.store'), [
                'name' => 'Pelanggan Demo',
                'phone' => '0811111111',
            ])
            ->assertSessionHasNoErrors();

        $customer = Customer::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.orders.store'), [
                'customer_id' => $customer->id,
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 2,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors()
            ->assertRedirect();

        $order = $customer->orders()->firstOrFail();
        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'pending',
            'grand_total' => 700000,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.orders.process', $order), [
                'warehouse_id' => $warehouse->id,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('orders', [
            'id' => $order->id,
            'status' => 'processing',
        ]);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_on_hand' => 8,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'type' => 'sale',
            'quantity' => -2,
        ]);
    }
}
