<?php

namespace App\Repositories;

use App\Models\Warehouse;
use App\Repositories\Contracts\WarehouseRepository;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

class EloquentWarehouseRepository implements WarehouseRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator
    {
        return Warehouse::query()
            ->withCount('inventories')
            ->when($search, fn ($query, string $search) => $query
                ->where('name', 'like', "%{$search}%")
                ->orWhere('code', 'like', "%{$search}%")
                ->orWhere('city', 'like', "%{$search}%"))
            ->latest()
            ->paginate($perPage)
            ->withQueryString();
    }

    public function create(array $data): Warehouse
    {
        return Warehouse::query()->create($data);
    }
}
