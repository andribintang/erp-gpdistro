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

    public function transfer(
        Inventory $from,
        int $toWarehouseId,
        int $quantity,
        string $notes,
        ?Authenticatable $actor,
    ): void {
        DB::transaction(function () use ($from, $toWarehouseId, $quantity, $notes, $actor): void {
            if ($quantity <= 0) {
                throw ValidationException::withMessages([
                    'quantity' => 'Qty transfer harus lebih dari nol.',
                ]);
            }

            $source = $this->inventories->lockForUpdate($from->getKey());
            $available = $source->stock_on_hand - $source->reserved_stock;

            if ($quantity > $available) {
                throw ValidationException::withMessages([
                    'quantity' => 'Stok tersedia tidak mencukupi untuk transfer.',
                ]);
            }

            $destination = Inventory::query()->firstOrCreate(
                [
                    'product_id' => $source->product_id,
                    'warehouse_id' => $toWarehouseId,
                ],
                [
                    'stock_on_hand' => 0,
                    'reserved_stock' => 0,
                    'minimum_stock' => 0,
                ],
            );
            $destination = $this->inventories->lockForUpdate($destination->getKey());

            $sourceBefore = $source->stock_on_hand;
            $sourceAfter = $sourceBefore - $quantity;
            $destBefore = $destination->stock_on_hand;
            $destAfter = $destBefore + $quantity;

            $source->update(['stock_on_hand' => $sourceAfter]);
            $destination->update(['stock_on_hand' => $destAfter]);

            $reference = 'TRF-'.now()->format('Ymd-His');
            $actorId = $actor?->getAuthIdentifier();

            $source->movements()->create([
                'product_id' => $source->product_id,
                'warehouse_id' => $source->warehouse_id,
                'created_by' => $actorId,
                'type' => 'transfer',
                'quantity' => -$quantity,
                'stock_before' => $sourceBefore,
                'stock_after' => $sourceAfter,
                'notes' => "{$reference}: {$notes} (keluar)",
            ]);

            $destination->movements()->create([
                'product_id' => $destination->product_id,
                'warehouse_id' => $destination->warehouse_id,
                'created_by' => $actorId,
                'type' => 'transfer',
                'quantity' => $quantity,
                'stock_before' => $destBefore,
                'stock_after' => $destAfter,
                'notes' => "{$reference}: {$notes} (masuk)",
            ]);
        });
    }
}
