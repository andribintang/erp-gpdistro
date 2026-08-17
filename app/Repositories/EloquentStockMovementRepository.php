<?php

namespace App\Repositories;

use App\Models\StockMovement;
use App\Repositories\Contracts\StockMovementRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentStockMovementRepository implements StockMovementRepository
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator
    {
        return StockMovement::query()
            ->with(['product', 'warehouse', 'creator'])
            ->when($filters['search'] ?? null, function ($query, string $search): void {
                $query->where(function ($inner) use ($search): void {
                    $inner->where('notes', 'like', "%{$search}%")
                        ->orWhereHas('product', fn ($product) => $product
                            ->where('name', 'like', "%{$search}%")
                            ->orWhere('sku', 'like', "%{$search}%"));
                });
            })
            ->when($filters['type'] ?? null, fn ($query, string $type) => $query->where('type', $type))
            ->when($filters['warehouse_id'] ?? null, fn ($query, int $id) => $query->where('warehouse_id', $id))
            ->when($filters['date_from'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '>=', $date))
            ->when($filters['date_to'] ?? null, fn ($query, string $date) => $query->whereDate('created_at', '<=', $date))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }
}
