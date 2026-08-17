<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepository
{
    public function paginate(array $filters = [], int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'category'])
            ->withSum('inventories as total_stock', 'stock_on_hand')
            ->withSum('inventories as total_reserved', 'reserved_stock')
            ->when($filters['search'] ?? null, fn ($query, string $search) => $query
                ->where(fn ($inner) => $inner
                    ->where('name', 'like', "%{$search}%")
                    ->orWhere('sku', 'like', "%{$search}%")))
            ->when($filters['product_type'] ?? null, fn ($query, string $type) => $query->where('product_type', $type))
            ->when($filters['brand_id'] ?? null, fn ($query, int $id) => $query->where('brand_id', $id))
            ->when($filters['category_id'] ?? null, fn ($query, int $id) => $query->where('category_id', $id))
            ->when(isset($filters['is_active']) && $filters['is_active'] !== '', fn ($query) => $query
                ->where('is_active', filter_var($filters['is_active'], FILTER_VALIDATE_BOOLEAN)))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }
}
