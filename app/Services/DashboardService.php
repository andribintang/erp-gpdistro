<?php

namespace App\Services;

use App\Models\Inventory;
use App\Models\Order;
use App\Models\ProductionOrder;
use App\Models\PurchaseOrder;
use App\Models\StockMovement;
use Carbon\Carbon;
use Illuminate\Support\Collection;

class DashboardService
{
    public function stats(): array
    {
        $revenueMtd = (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year)
            ->sum('grand_total');

        $openOrders = Order::query()
            ->whereIn('status', ['pending', 'paid', 'processing'])
            ->count();

        $lowStock = Inventory::query()
            ->whereColumn('stock_on_hand', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->count();

        $productionQueue = ProductionOrder::query()
            ->where('status', '!=', 'done')
            ->count();

        $prevRevenue = (float) Order::query()
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->whereMonth('created_at', now()->subMonth()->month)
            ->whereYear('created_at', now()->subMonth()->year)
            ->sum('grand_total');

        $revenueTrend = $this->percentChange($prevRevenue, $revenueMtd);

        return [
            [
                'label' => 'Pendapatan (Bulan Berjalan)',
                'value' => $this->formatIdr($revenueMtd),
                'trend' => $revenueTrend,
            ],
            [
                'label' => 'Sales Order Terbuka',
                'value' => (string) $openOrders,
                'trend' => $openOrders > 0 ? "{$openOrders} menunggu tindak lanjut" : 'Tidak ada antrian',
            ],
            [
                'label' => 'SKU Stok Menipis',
                'value' => (string) $lowStock,
                'trend' => $lowStock > 0 ? 'Perlu restock' : 'Aman',
            ],
            [
                'label' => 'Antrian Produksi',
                'value' => (string) $productionQueue,
                'trend' => $productionQueue > 0 ? 'Ada backlog aktif' : 'Tidak ada backlog',
            ],
        ];
    }

    public function revenueChart(int $days = 14): array
    {
        $start = now()->subDays($days - 1)->startOfDay();

        $rows = Order::query()
            ->selectRaw('DATE(created_at) as day, SUM(grand_total) as total')
            ->whereIn('status', ['paid', 'processing', 'completed'])
            ->where('created_at', '>=', $start)
            ->groupBy('day')
            ->orderBy('day')
            ->pluck('total', 'day');

        $chart = [];
        $max = 0.0;

        for ($i = 0; $i < $days; $i++) {
            $date = $start->copy()->addDays($i);
            $key = $date->toDateString();
            $value = (float) ($rows[$key] ?? 0);
            $max = max($max, $value);
            $chart[] = [
                'label' => $date->locale('id')->isoFormat('D MMM'),
                'value' => $value,
            ];
        }

        return ['points' => $chart, 'max' => $max > 0 ? $max : 1];
    }

    public function operationalSummary(): array
    {
        $ordersThisMonth = Order::query()
            ->whereMonth('created_at', now()->month)
            ->whereYear('created_at', now()->year);

        $total = (clone $ordersThisMonth)->count();
        $completed = (clone $ordersThisMonth)->where('status', 'completed')->count();
        $fulfillmentRate = $total > 0 ? round(($completed / $total) * 100, 1) : 0;

        $pendingPo = PurchaseOrder::query()->where('status', 'submitted')->count();
        $lowStock = Inventory::query()
            ->whereColumn('stock_on_hand', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->count();

        return [
            'fulfillment_rate' => $fulfillmentRate,
            'pending_po' => $pendingPo,
            'low_stock' => $lowStock,
            'orders_month' => $total,
        ];
    }

    public function alerts(): array
    {
        $alerts = [];
        $lowStock = Inventory::query()
            ->with('product')
            ->whereColumn('stock_on_hand', '<=', 'minimum_stock')
            ->where('minimum_stock', '>', 0)
            ->limit(3)
            ->get();

        foreach ($lowStock as $inventory) {
            $alerts[] = [
                'type' => 'warning',
                'message' => "{$inventory->product->name} stok {$inventory->stock_on_hand} (min {$inventory->minimum_stock}).",
            ];
        }

        $pendingPo = PurchaseOrder::query()->where('status', 'submitted')->count();
        if ($pendingPo > 0) {
            $alerts[] = [
                'type' => 'info',
                'message' => "{$pendingPo} purchase order menunggu persetujuan.",
            ];
        }

        if ($alerts === []) {
            $alerts[] = [
                'type' => 'success',
                'message' => 'Tidak ada peringatan kritis pada shift ini.',
            ];
        }

        return $alerts;
    }

    public function recentActivities(): Collection
    {
        $movements = StockMovement::query()
            ->with(['product', 'warehouse'])
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (StockMovement $movement) => [
                'at' => $movement->created_at,
                'title' => $this->movementTitle($movement),
                'meta' => $movement->warehouse?->name ?? 'Gudang',
            ]);

        $orders = Order::query()
            ->with('customer')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (Order $order) => [
                'at' => $order->created_at,
                'title' => "{$order->order_number} · ".($this->orderStatusLabel($order->status)),
                'meta' => $order->customer?->name ?? 'Walk-in',
            ]);

        $purchaseOrders = PurchaseOrder::query()
            ->with('supplier')
            ->latest()
            ->limit(5)
            ->get()
            ->map(fn (PurchaseOrder $po) => [
                'at' => $po->created_at,
                'title' => "{$po->purchase_number} · {$this->poStatusLabel($po->status)}",
                'meta' => $po->supplier?->name ?? 'Supplier',
            ]);

        return $movements
            ->concat($orders)
            ->concat($purchaseOrders)
            ->sortByDesc('at')
            ->take(6)
            ->values()
            ->map(fn (array $item) => [
                ...$item,
                'ago' => Carbon::parse($item['at'])->locale('id')->diffForHumans(),
            ]);
    }

    private function movementTitle(StockMovement $movement): string
    {
        $product = $movement->product?->name ?? 'Produk';
        $qty = abs($movement->quantity);

        return match ($movement->type) {
            'sale' => "Penjualan {$product} ({$qty} unit)",
            'receipt' => "Penerimaan {$product} (+{$qty})",
            'adjustment' => "Penyesuaian {$product}",
            default => "Pergerakan stok {$product}",
        };
    }

    private function orderStatusLabel(string $status): string
    {
        return match ($status) {
            'pending' => 'Menunggu',
            'paid' => 'Lunas',
            'processing' => 'Diproses',
            'completed' => 'Selesai',
            'cancelled' => 'Dibatalkan',
            default => ucfirst($status),
        };
    }

    private function poStatusLabel(string $status): string
    {
        return match ($status) {
            'submitted' => 'Diajukan',
            'approved' => 'Disetujui',
            'received' => 'Diterima',
            default => ucfirst($status),
        };
    }

    private function formatIdr(float $amount): string
    {
        return 'Rp '.number_format($amount, 0, ',', '.');
    }

    private function percentChange(float $previous, float $current): string
    {
        if ($previous <= 0) {
            return $current > 0 ? '+100% vs bulan lalu' : 'Belum ada data bulan lalu';
        }

        $change = (($current - $previous) / $previous) * 100;
        $sign = $change >= 0 ? '+' : '';

        return $sign.number_format($change, 1, ',', '.').'% vs bulan lalu';
    }
}
