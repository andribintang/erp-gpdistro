<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\OrderItem;
use App\Models\Payment;
use App\Models\Product;
use App\Models\Shipment;
use App\Repositories\Contracts\OrderRepository;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use Illuminate\Validation\ValidationException;

class OrderService
{
    public function __construct(private readonly OrderRepository $orders)
    {
    }

    public function create(array $data): Order
    {
        return DB::transaction(function () use ($data): Order {
            $items = $data['items'];
            $reserveStock = (bool) ($data['reserve_stock'] ?? false);
            unset($data['items'], $data['warehouse_id'], $data['reserve_stock']);

            $lineItems = $this->buildLineItems($items);
            $totals = $this->calculateTotals($lineItems, $data);

            $order = $this->orders->create([
                ...$data,
                'order_number' => 'SO-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'channel' => $data['channel'] ?? 'erp',
                'status' => 'pending',
                ...$totals,
            ]);

            foreach ($lineItems as $line) {
                $order->items()->create($line);
            }

            $order = $order->load(['customer', 'items.product']);

            if ($reserveStock) {
                $this->reserve($order);
            }

            return $order->refresh()->load(['customer', 'items.product']);
        });
    }

    public function reserve(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Hanya pesanan menunggu yang dapat direservasi.',
                ]);
            }

            foreach ($lockedOrder->items as $item) {
                if (! $item->product_id || $this->itemHasReservations($item)) {
                    continue;
                }

                $this->allocateReservation($item, "Reservasi {$lockedOrder->order_number}.");
            }

            return $lockedOrder->refresh()->load(['customer', 'items.product']);
        });
    }

    public function recordPayment(Order $order, array $data): Payment
    {
        return DB::transaction(function () use ($order, $data): Payment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (in_array($lockedOrder->status, ['cancelled', 'completed'], true)) {
                throw ValidationException::withMessages([
                    'amount' => 'Pesanan ini tidak dapat menerima pembayaran.',
                ]);
            }

            $payment = $lockedOrder->payments()->create([
                'payment_number' => 'PAY-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'method' => $data['method'],
                'status' => 'verified',
                'amount' => $data['amount'],
                'paid_at' => $data['paid_at'] ?? now(),
            ]);

            $lockedOrder->refresh();

            if ($lockedOrder->paid_total >= (float) $lockedOrder->grand_total && $lockedOrder->status === 'pending') {
                $lockedOrder->update(['status' => 'paid']);
            }

            return $payment;
        });
    }

    public function recordShipment(Order $order, array $data): Shipment
    {
        return DB::transaction(function () use ($order, $data): Shipment {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, ['processing', 'completed', 'paid'], true)) {
                throw ValidationException::withMessages([
                    'courier' => 'Pesanan harus diproses sebelum pengiriman.',
                ]);
            }

            return $lockedOrder->shipments()->create([
                'shipment_number' => 'SHP-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                'courier' => $data['courier'],
                'service' => $data['service'] ?? null,
                'tracking_number' => $data['tracking_number'] ?? null,
                'status' => $data['status'] ?? 'shipped',
                'shipping_cost' => $data['shipping_cost'] ?? $lockedOrder->shipping_cost,
                'shipped_at' => now(),
            ]);
        });
    }

    public function markPaid(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if ($lockedOrder->status !== 'pending') {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan ini tidak dapat ditandai lunas.',
                ]);
            }

            $balance = $lockedOrder->balance_due;
            if ($balance > 0) {
                $lockedOrder->payments()->create([
                    'payment_number' => 'PAY-'.now()->format('Ymd-His').'-'.Str::upper(Str::random(4)),
                    'method' => 'manual_transfer',
                    'status' => 'verified',
                    'amount' => $balance,
                    'paid_at' => now(),
                ]);
            }

            $lockedOrder->update(['status' => 'paid']);

            return $lockedOrder->refresh();
        });
    }

    public function process(Order $order, ?int $warehouseId = null): Order
    {
        return DB::transaction(function () use ($order, $warehouseId): Order {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, ['pending', 'paid'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan ini tidak dapat diproses.',
                ]);
            }

            foreach ($lockedOrder->items as $item) {
                if (! $item->product_id) {
                    continue;
                }

                if ($this->itemHasReservations($item)) {
                    $this->fulfillReservation($item, "Penjualan {$lockedOrder->order_number}.");
                } else {
                    $this->deductStock($item->product_id, $item->qty, $warehouseId, "Penjualan {$lockedOrder->order_number}.");
                }
            }

            $lockedOrder->update(['status' => 'processing']);

            return $lockedOrder->refresh()->load(['customer', 'items.product']);
        });
    }

    public function complete(Order $order): Order
    {
        return $this->transition($order, ['processing'], 'completed', 'status', 'Pesanan ini tidak dapat diselesaikan.');
    }

    public function cancel(Order $order): Order
    {
        return DB::transaction(function () use ($order): Order {
            $lockedOrder = Order::query()->with('items')->lockForUpdate()->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, ['pending', 'paid'], true)) {
                throw ValidationException::withMessages([
                    'status' => 'Pesanan ini tidak dapat dibatalkan.',
                ]);
            }

            foreach ($lockedOrder->items as $item) {
                if ($this->itemHasReservations($item)) {
                    $this->releaseReservation($item, "Batal {$lockedOrder->order_number}.");
                }
            }

            $lockedOrder->update(['status' => 'cancelled']);

            return $lockedOrder->refresh();
        });
    }

    private function buildLineItems(array $items): \Illuminate\Support\Collection
    {
        return collect($items)->map(function (array $item): array {
            $product = Product::query()->findOrFail($item['product_id']);
            $price = $item['price'] ?? $product->price;
            $qty = (int) $item['qty'];

            return [
                'product_id' => $product->id,
                'product_name_snapshot' => $product->name,
                'qty' => $qty,
                'price' => $price,
                'subtotal' => $qty * $price,
                'meta' => null,
            ];
        });
    }

    private function calculateTotals(\Illuminate\Support\Collection $lineItems, array $data): array
    {
        $subtotal = $lineItems->sum('subtotal');
        $shipping = (float) ($data['shipping_cost'] ?? 0);
        $discount = (float) ($data['discount_amount'] ?? 0);

        return [
            'subtotal' => $subtotal,
            'shipping_cost' => $shipping,
            'discount_amount' => $discount,
            'grand_total' => max(0, $subtotal + $shipping - $discount),
        ];
    }

    private function itemHasReservations(OrderItem $item): bool
    {
        return ! empty($item->meta['reservations'] ?? []);
    }

    private function allocateReservation(OrderItem $item, string $notes): void
    {
        $remaining = $item->qty;
        $reservations = [];

        $inventories = Inventory::query()
            ->where('product_id', $item->product_id)
            ->orderByRaw('(stock_on_hand - reserved_stock) DESC')
            ->lockForUpdate()
            ->get();

        foreach ($inventories as $inventory) {
            if ($remaining <= 0) {
                break;
            }

            $available = $inventory->stock_on_hand - $inventory->reserved_stock;
            if ($available <= 0) {
                continue;
            }

            $reserveQty = min($available, $remaining);
            $inventory->increment('reserved_stock', $reserveQty);
            $inventory->movements()->create([
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'created_by' => auth()->id(),
                'type' => 'reservation',
                'quantity' => $reserveQty,
                'stock_before' => $inventory->stock_on_hand,
                'stock_after' => $inventory->stock_on_hand,
                'notes' => $notes,
            ]);

            $reservations[] = [
                'inventory_id' => $inventory->id,
                'qty' => $reserveQty,
            ];
            $remaining -= $reserveQty;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => "Stok tidak cukup untuk reservasi {$item->product_name_snapshot}.",
            ]);
        }

        $item->update(['meta' => ['reservations' => $reservations]]);
    }

    private function fulfillReservation(OrderItem $item, string $notes): void
    {
        foreach ($item->meta['reservations'] as $reservation) {
            $inventory = Inventory::query()->lockForUpdate()->findOrFail($reservation['inventory_id']);
            $qty = (int) $reservation['qty'];
            $stockBefore = $inventory->stock_on_hand;

            if ($inventory->reserved_stock < $qty || $stockBefore < $qty) {
                throw ValidationException::withMessages([
                    'items' => 'Reservasi stok tidak valid saat pemrosesan.',
                ]);
            }

            $stockAfter = $stockBefore - $qty;
            $inventory->update([
                'stock_on_hand' => $stockAfter,
                'reserved_stock' => $inventory->reserved_stock - $qty,
            ]);

            $inventory->movements()->create([
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'created_by' => auth()->id(),
                'type' => 'sale',
                'quantity' => -$qty,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
            ]);
        }

        $item->update(['meta' => ['reservations' => [], 'fulfilled_at' => now()->toIso8601String()]]);
    }

    private function releaseReservation(OrderItem $item, string $notes): void
    {
        foreach ($item->meta['reservations'] ?? [] as $reservation) {
            $inventory = Inventory::query()->lockForUpdate()->findOrFail($reservation['inventory_id']);
            $qty = (int) $reservation['qty'];
            $inventory->decrement('reserved_stock', min($qty, $inventory->reserved_stock));

            $inventory->movements()->create([
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'created_by' => auth()->id(),
                'type' => 'release',
                'quantity' => -$qty,
                'stock_before' => $inventory->stock_on_hand,
                'stock_after' => $inventory->stock_on_hand,
                'notes' => $notes,
            ]);
        }

        $item->update(['meta' => ['reservations' => []]]);
    }

    private function transition(Order $order, array $from, string $to, string $field, string $message): Order
    {
        return DB::transaction(function () use ($order, $from, $to, $field, $message): Order {
            $lockedOrder = Order::query()->lockForUpdate()->findOrFail($order->getKey());

            if (! in_array($lockedOrder->status, $from, true)) {
                throw ValidationException::withMessages([$field => $message]);
            }

            $lockedOrder->update(['status' => $to]);

            return $lockedOrder->refresh();
        });
    }

    private function deductStock(int $productId, int $qty, ?int $warehouseId, string $notes): void
    {
        $remaining = $qty;

        $inventories = Inventory::query()
            ->where('product_id', $productId)
            ->when($warehouseId, fn ($builder) => $builder->where('warehouse_id', $warehouseId))
            ->orderByRaw('(stock_on_hand - reserved_stock) DESC')
            ->lockForUpdate()
            ->get();

        foreach ($inventories as $inventory) {
            if ($remaining <= 0) {
                break;
            }

            $available = $inventory->stock_on_hand - $inventory->reserved_stock;
            if ($available <= 0) {
                continue;
            }

            $deduct = min($available, $remaining);
            $stockBefore = $inventory->stock_on_hand;
            $stockAfter = $stockBefore - $deduct;

            $inventory->update(['stock_on_hand' => $stockAfter]);
            $inventory->movements()->create([
                'product_id' => $inventory->product_id,
                'warehouse_id' => $inventory->warehouse_id,
                'created_by' => auth()->id(),
                'type' => 'sale',
                'quantity' => -$deduct,
                'stock_before' => $stockBefore,
                'stock_after' => $stockAfter,
                'notes' => $notes,
            ]);

            $remaining -= $deduct;
        }

        if ($remaining > 0) {
            throw ValidationException::withMessages([
                'items' => 'Stok tidak mencukupi untuk memproses pesanan ini.',
            ]);
        }
    }
}
