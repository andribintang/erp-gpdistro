<?php

namespace Tests\Feature\Admin;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryTransferTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_transfer_stock_between_warehouses(): void
    {
        Role::findOrCreate('Manager');
        $user = User::factory()->create();
        $user->assignRole('Manager');

        $product = Product::query()->create([
            'sku' => 'TRF-001',
            'name' => 'Product Transfer',
            'slug' => 'product-transfer',
            'product_type' => 'spare_part',
            'price' => 100000,
        ]);
        $from = Warehouse::query()->create(['code' => 'WH-A', 'name' => 'Gudang A', 'is_active' => true]);
        $to = Warehouse::query()->create(['code' => 'WH-B', 'name' => 'Gudang B', 'is_active' => true]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $from->id,
            'stock_on_hand' => 20,
            'reserved_stock' => 0,
            'minimum_stock' => 0,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.inventory.transfer', $inventory), [
                'to_warehouse_id' => $to->id,
                'quantity' => 5,
                'notes' => 'Alokasi cabang',
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $from->id,
            'stock_on_hand' => 15,
        ]);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $to->id,
            'stock_on_hand' => 5,
        ]);
    }
}
