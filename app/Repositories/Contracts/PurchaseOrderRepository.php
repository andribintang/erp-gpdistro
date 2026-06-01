<?php

namespace App\Repositories\Contracts;

use App\Models\PurchaseOrder;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface PurchaseOrderRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): PurchaseOrder;
}
