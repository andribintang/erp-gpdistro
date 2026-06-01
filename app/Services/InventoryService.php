<?php

namespace App\Services;

use App\Models\Inventory;
use App\Repositories\Contracts\InventoryRepository;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Support\Facades\DB;
use Illuminate\Validation\ValidationException;

class InventoryService
{
    public function __construct(private readonly InventoryRepository $inventories)
    {
    }

    public function adjust(Inventory $inventory, int $quantity, string $notes, ?Authenticatable $actor): Inventory
    {
        return DB::transaction(function () use ($inventory, $quantity, $notes, $actor): Inventory {
            $lockedInventory = $this->inventories->lockForUpdate($inventory->getKey());
            $stockAfter = $lockedInventory->stock_on_hand + $quantity;

            if ($stockAfter < 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Penyesuaian membuat stok menjadi negatif.',
                ]);
            }

            $stockBefore = $lockedInventory->stock_on_hand;
            $lockedInventory->update(['stock_on_hand' => $stockAfter]);
            $lockedInventory->movements()->create([
                'product_id' => $lockedInventory->product_id,
                'warehouse_id' => $lockedInventory->warehouse_id,
                'created_by' => $actor?->getAuthIdentifier(),
                'type' => 'adjustment',
                'quantity' => $quantity,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
            ]);

            return $lockedInventory->refresh();
        });
    }
}
