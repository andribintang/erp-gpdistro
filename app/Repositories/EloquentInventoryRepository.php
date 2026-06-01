<?php

namespace App\Repositories;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentInventoryRepository implements InventoryRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Inventory::query()
            ->with(['product.brand', 'product.category', 'warehouse'])
            ->when($search, function ($query, string $search): void {
                $query->whereHas('product', fn ($product) => $product
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%"))
                    ->orWhereHas('warehouse', fn ($warehouse) => $warehouse
                        ->where('name', 'like', "%{$search}%")
                        ->orWhere('code', 'like', "%{$search}%"));
            })
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
            ->count();
    }
}
