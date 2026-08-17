<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInventoryRepository implements InventoryRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Inventory::query()
            ->with(['product.brand', 'product.category', 'warehouse'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->whereHas('product', fn ($product) => $product
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('sku', 'like', "%{$search}%"))
                        ->orWhereHas('warehouse', fn ($warehouse) => $warehouse
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('code', 'like', "%{$search}%"));
                });
            })
            ->when($filters['warehouse_id'] ?? null, fn ($query, int $id) => $query->where('warehouse_id', $id))
            ->when(filter_var($filters['low_stock'] ?? false, FILTER_VALIDATE_BOOLEAN), function ($query): void {
                $query->whereColumn('stock_on_hand', '<=', 'minimum_stock')
                    ->where('minimum_stock', '>', 0);
            })
            ->when($filters['product_type'] ?? null, fn ($query, string $type) => $query
                ->whereHas('product', fn ($product) => $product->where('product_type', $type)))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function lockForUpdate(int $inventoryId): Inventory
    {
        return Inventory::query()->lockForUpdate()->findOrFail($inventoryId);
    }

    public function lowStockCount(): int
    {
        return Inventory::query()
            ->whereColumn('stock_on_hand', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->count();
    }
}
