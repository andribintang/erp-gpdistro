<?php

namespace App\Repositories\Contracts;

use App\Models\Inventory;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface InventoryRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator;

    public function lockForUpdate(int $inventoryId): Inventory;

    public function lowStockCount(): int;
}
