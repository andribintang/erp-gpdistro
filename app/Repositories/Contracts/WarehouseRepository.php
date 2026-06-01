<?php

namespace App\Repositories\Contracts;

use App\Models\Warehouse;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface WarehouseRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Warehouse;
}
