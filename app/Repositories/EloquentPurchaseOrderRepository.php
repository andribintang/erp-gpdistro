<?php

namespace App\Repositories;

use App\Models\PurchaseOrder;
use App\Repositories\Contracts\PurchaseOrderRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentPurchaseOrderRepository implements PurchaseOrderRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return PurchaseOrder::query()
            ->with(['supplier', 'items.product'])
            ->when($search, fn ($query, string $search) => $query
                ->where('purchase_number', 'like', "%{$search}%")
                ->orWhereHas('supplier', fn ($supplier) => $supplier->where('name', 'like', "%{$search}%")))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): PurchaseOrder
    {
        return PurchaseOrder::query()->create($data);
    }
}
