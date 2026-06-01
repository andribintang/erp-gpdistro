<?php

namespace App\Services;

use App\Models\PurchaseOrder;
use App\Models\Inventory;
use App\Models\GoodsReceipt;
use App\Models\PurchaseOrderItem;
use App\Repositories\Contracts\PurchaseOrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class PurchaseOrderService
{
    public function __construct(private readonly PurchaseOrderRepository $purchaseOrders)
    {
    }

    public function create(array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($data): PurchaseOrder {
            $items = $data['items'];
            unset($data['items']);

            $data['created_by'] = auth()->id();
            $data['purchase_number'] = 'PO-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4));
            $data['grand_total'] = collect($items)->sum(fn (array $item) => $item['qty'] * $item['unit_price']);

            $purchaseOrder = $this->purchaseOrders->create($data);
            $purchaseOrder->items()->createMany(collect($items)->map(fn (array $item) => [
                ...$item,
                'subtotal' => $item['qty'] * $item['unit_price'],
            ])->all());

            return $purchaseOrder->load(['supplier', 'items.product']);
        });
    }

    public function approve(PurchaseOrder $purchaseOrder): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder): PurchaseOrder {
            $lockedOrder = PurchaseOrder::query()->lockForUpdate()->findOrFail($purchaseOrder->getKey());

            if (! in_array($lockedOrder->status, ['draft', 'submitted'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Purchase order ini tidak dapat disetujui.',
                ]);
            }

            $lockedOrder->update(['status' => 'approved']);

            return $lockedOrder->refresh();
        });
    }

    public function receive(PurchaseOrder $purchaseOrder, array $data): PurchaseOrder
    {
        return DB::transaction(function () use ($purchaseOrder, $data): PurchaseOrder {
            $lockedOrder = PurchaseOrder::query()
                ->with('items')
                ->lockForUpdate()
                ->findOrFail($purchaseOrder->getKey());

            if ($lockedOrder->status !== 'approved') {
                throw ValidationException::withMessages([
                    'warehouse_id' => 'Purchase order harus disetujui sebelum penerimaan.',
                ]);
            }

            $receipt = GoodsReceipt::query()->create([
                'purchase_order_id' => $lockedOrder->id,
                'warehouse_id' => $data['warehouse_id'],
                'received_by' => auth()->id(),
                'receipt_number' => 'GR-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'received_at' => now(),
                'notes' => $data['notes'] ?? null,
            ]);

            foreach ($data['items'] as $receivedItem) {
                $item = PurchaseOrderItem::query()
                    ->where('purchase_order_id', $lockedOrder->id)
                    ->lockForUpdate()
                    ->findOrFail($receivedItem['purchase_order_item_id']);

                if ($receivedItem['quantity'] > $item->remaining_qty) {
                    throw ValidationException::withMessages([
                        'items' => "Penerimaan {$item->product_id} melebihi sisa PO.",
                    ]);
                }

                $inventory = Inventory::query()->firstOrCreate(
                    [
                        'product_id' => $item->product_id,
                        'warehouse_id' => $data['warehouse_id'],
                    ],
                    [
                        'stock_on_hand' => 0,
                        'reserved_stock' => 0,
                        'minimum_stock' => 0,
                    ],
                );
                $inventory = Inventory::query()->lockForUpdate()->findOrFail($inventory->getKey());
                $stockBefore = $inventory->stock_on_hand;
                $stockAfter = $stockBefore + $receivedItem['quantity'];

                $inventory->update(['stock_on_hand' => $stockAfter]);
                $item->increment('received_qty', $receivedItem['quantity']);
                $receipt->items()->create([
                    'purchase_order_item_id' => $item->id,
                    'product_id' => $item->product_id,
                    'quantity' => $receivedItem['quantity'],
                ]);
                $inventory->movements()->create([
                    'product_id' => $item->product_id,
                    'warehouse_id' => $data['warehouse_id'],
                    'created_by' => auth()->id(),
                    'type' => 'receipt',
                    'quantity' => $receivedItem['quantity'],
                    'stock_before' => $stockBefore,
                    'stock_after' => $stockAfter,
                    'notes' => "Penerimaan {$receipt->receipt_number} untuk {$lockedOrder->purchase_number}.",
                ]);
            }

            $hasRemainingItems = $lockedOrder->items()->whereColumn('received_qty', '<', 'qty')->exists();
            $lockedOrder->update(['status' => $hasRemainingItems ? 'approved' : 'received']);

            return $lockedOrder->refresh();
        });
    }
}
