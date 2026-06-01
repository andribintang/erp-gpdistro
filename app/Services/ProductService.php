<?php

namespace App\Services;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class ProductService
{
    public function __construct(private readonly ProductRepository $products)
    {
    }

    public function create(array $data): Product
    {
        return DB::transaction(function () use ($data): Product {
            $warehouseId = $data['warehouse_id'];
            $initialStock = $data['initial_stock'];
            $minimumStock = $data['minimum_stock'];

            unset($data['warehouse_id'], $data['initial_stock'], $data['minimum_stock']);
            $data['slug'] = Str::slug($data['name'].'-'.$data['sku']);

            $product = $this->products->create($data);
            $inventory = $product->inventories()->create([
                'warehouse_id' => $warehouseId,
                'stock_on_hand' => $initialStock,
                'reserved_stock' => 0,
                'minimum_stock' => $minimumStock,
            ]);

            if ($initialStock !== 0) {
                $inventory->movements()->create([
                    'product_id' => $product->getKey(),
                    'warehouse_id' => $warehouseId,
                    'created_by' => auth()->id(),
                    'type' => 'initial',
                    'quantity' => $initialStock,
                    'stock_before' => 0,
                    'stock_after' => $initialStock,
                    'notes' => 'Saldo awal saat SKU dibuat.',
                ]);
            }

            return $product;
        });
    }

    public function update(Product $product, array $data): Product
    {
        $data['slug'] = Str::slug($data['name'].'-'.$data['sku']);
        $product->update($data);

        return $product->refresh();
    }

    public function delete(Product $product): void
    {
        $hasStock = $product->inventories()->where('stock_on_hand', '>', 0)->exists();
        $hasPurchaseItems = $product->purchaseOrderItems()->exists();

        if ($hasStock || $hasPurchaseItems) {
            $product->update(['is_active' => false]);

            return;
        }

        $product->delete();
    }
}
