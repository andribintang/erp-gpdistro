<?php

namespace App\Repositories;

use App\Models\Product;
use App\Repositories\Contracts\ProductRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentProductRepository implements ProductRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Product::query()
            ->with(['brand', 'category'])
            ->withSum('inventories as total_stock', 'stock_on_hand')
            ->when($search, fn ($query, string $search) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('sku', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Product
    {
        return Product::query()->create($data);
    }
}
