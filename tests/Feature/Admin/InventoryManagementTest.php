<?php

namespace Tests\Feature\Admin;

use App\Models\Inventory;
use App\Models\Product;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class InventoryManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_view_inventory(): void
    {
        $user = $this->userWithRole('Manager');

        $this->actingAs($user)
            ->get(route('admin.inventory.index'))
            ->assertOk();
    }

    public function test_user_without_staff_role_cannot_view_inventory(): void
    {
        $user = User::factory()->create();

        $this->actingAs($user)
            ->get(route('admin.inventory.index'))
            ->assertForbidden();
    }

    public function test_owner_can_create_warehouse(): void
    {
        $user = $this->userWithRole('Owner');

        $this->actingAs($user)
            ->post(route('admin.warehouses.store'), [
                'code' => 'WH-JKT',
                'name' => 'Gudang Jakarta',
                'city' => 'Jakarta',
                'is_active' => true,
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('warehouses', [
            'code' => 'WH-JKT',
            'name' => 'Gudang Jakarta',
        ]);
    }

    public function test_manager_can_create_product_with_initial_stock_movement(): void
    {
        $user = $this->userWithRole('Manager');
        $warehouse = Warehouse::query()->create([
            'code' => 'WH-BDG',
            'name' => 'Gudang Bandung',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('admin.products.store'), [
                'sku' => 'JRS-001',
                'name' => 'Jersey Racing',
                'product_type' => 'apparel',
                'price' => 250000,
                'warehouse_id' => $warehouse->id,
                'initial_stock' => 12,
                'minimum_stock' => 3,
            ])
            ->assertSessionHasNoErrors();

        $product = Product::query()->where('sku', 'JRS-001')->firstOrFail();
        $inventory = Inventory::query()->where('product_id', $product->id)->firstOrFail();

        $this->assertSame(12, $inventory->stock_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'initial',
            'quantity' => 12,
            'stock_after' => 12,
        ]);
    }

    public function test_manager_can_adjust_inventory_and_cannot_make_stock_negative(): void
    {
        $user = $this->userWithRole('Manager');
        $warehouse = Warehouse::query()->create([
            'code' => 'WH-SBY',
            'name' => 'Gudang Surabaya',
            'is_active' => true,
        ]);
        $product = Product::query()->create([
            'sku' => 'SPR-001',
            'name' => 'Spare Part',
            'slug' => 'spare-part-spr-001',
            'product_type' => 'spare_part',
            'price' => 100000,
        ]);
        $inventory = Inventory::query()->create([
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_on_hand' => 5,
            'reserved_stock' => 0,
            'minimum_stock' => 1,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.inventory.adjust', $inventory), [
                'quantity' => -2,
                'notes' => 'Koreksi hasil opname.',
            ])
            ->assertSessionHasNoErrors();

        $this->assertSame(3, $inventory->refresh()->stock_on_hand);
        $this->assertDatabaseHas('stock_movements', [
            'inventory_id' => $inventory->id,
            'type' => 'adjustment',
            'quantity' => -2,
            'stock_after' => 3,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.inventory.adjust', $inventory), [
                'quantity' => -4,
                'notes' => 'Koreksi invalid.',
            ])
            ->assertSessionHasErrors('quantity');

        $this->assertSame(3, $inventory->refresh()->stock_on_hand);
    }

    private function userWithRole(string $role): User
    {
        Role::findOrCreate($role);

        $user = User::factory()->create();
        $user->assignRole($role);

        return $user;
    }
}
