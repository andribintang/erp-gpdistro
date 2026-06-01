<?php

namespace Tests\Feature\Admin;

use App\Models\Product;
use App\Models\Supplier;
use App\Models\User;
use App\Models\Warehouse;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Spatie\Permission\Models\Role;
use Tests\TestCase;

class PurchasingManagementTest extends TestCase
{
    use RefreshDatabase;

    public function test_manager_can_create_supplier_and_purchase_order(): void
    {
        Role::findOrCreate('Manager');
        Role::findOrCreate('Owner');
        $user = User::factory()->create();
        $user->assignRole('Manager');
        $owner = User::factory()->create();
        $owner->assignRole('Owner');
        $product = Product::query()->create([
            'sku' => 'MAT-001',
            'name' => 'Material Jersey',
            'slug' => 'material-jersey-mat-001',
            'product_type' => 'apparel',
            'price' => 50000,
        ]);
        $warehouse = Warehouse::query()->create([
            'code' => 'WH-RCV',
            'name' => 'Gudang Penerimaan',
            'is_active' => true,
        ]);

        $this->actingAs($user)
            ->post(route('admin.suppliers.store'), [
                'name' => 'Supplier Tekstil',
                'phone' => '08123456789',
            ])
            ->assertSessionHasNoErrors();

        $supplier = Supplier::query()->firstOrFail();

        $this->actingAs($user)
            ->post(route('admin.purchasing.store'), [
                'supplier_id' => $supplier->id,
                'order_date' => now()->toDateString(),
                'items' => [
                    [
                        'product_id' => $product->id,
                        'qty' => 4,
                        'unit_price' => 75000,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('purchase_orders', [
            'supplier_id' => $supplier->id,
            'created_by' => $user->id,
            'grand_total' => 300000,
        ]);
        $this->assertDatabaseHas('purchase_order_items', [
            'product_id' => $product->id,
            'qty' => 4,
            'subtotal' => 300000,
        ]);

        $purchaseOrder = $supplier->purchaseOrders()->firstOrFail();

        $this->actingAs($user)
            ->patch(route('admin.purchasing.receive', $purchaseOrder), [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    [
                        'purchase_order_item_id' => $purchaseOrder->items()->firstOrFail()->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertSessionHasErrors('warehouse_id');

        $this->actingAs($owner)
            ->patch(route('admin.purchasing.approve', $purchaseOrder))
            ->assertSessionHasNoErrors();

        $purchaseOrderItem = $purchaseOrder->items()->firstOrFail();

        $this->actingAs($user)
            ->patch(route('admin.purchasing.receive', $purchaseOrder), [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    [
                        'purchase_order_item_id' => $purchaseOrderItem->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('purchase_orders', ['id' => $purchaseOrder->id, 'status' => 'approved']);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_on_hand' => 2,
        ]);
        $this->assertDatabaseHas('stock_movements', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'type' => 'receipt',
            'quantity' => 2,
        ]);

        $this->actingAs($user)
            ->patch(route('admin.purchasing.receive', $purchaseOrder), [
                'warehouse_id' => $warehouse->id,
                'items' => [
                    [
                        'purchase_order_item_id' => $purchaseOrderItem->id,
                        'quantity' => 2,
                    ],
                ],
            ])
            ->assertSessionHasNoErrors();

        $this->assertDatabaseHas('purchase_orders', ['id' => $purchaseOrder->id, 'status' => 'received']);
        $this->assertDatabaseHas('inventories', [
            'product_id' => $product->id,
            'warehouse_id' => $warehouse->id,
            'stock_on_hand' => 4,
        ]);
        $this->assertDatabaseCount('goods_receipts', 2);
        $this->assertDatabaseCount('goods_receipt_items', 2);
    }
}
