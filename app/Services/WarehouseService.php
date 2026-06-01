<?php

namespace App\Services;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepository;

class WarehouseService
{
    public function __construct(private readonly WarehouseRepository $warehouses)
    {
    }

    public function create(array $data): Warehouse
    {
        return $this->warehouses->create($data);
    }

    public function update(Warehouse $warehouse, array $data): Warehouse
    {
        $warehouse->update($data);

        return $warehouse->refresh();
    }

    public function delete(Warehouse $warehouse): void
    {
        if ($warehouse->inventories()->where('stock_on_hand', '>', 0)->exists()) {
            throw new \InvalidArgumentException('Gudang tidak dapat dihapus karena masih memiliki stok.');
        }

        $warehouse->delete();
    }
}
