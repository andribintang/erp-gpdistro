<?php

namespace App\Repositories\Contracts;

use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface StockMovementRepository
{
    public function paginate(array $filters = [], int $perPage = 20): LengthAwarePaginator;
}
