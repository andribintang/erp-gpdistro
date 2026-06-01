<?php

namespace App\Repositories\Contracts;

use App\Models\Product;
use Illuminate\Contracts\Pagination\LengthAwarePaginator;

interface ProductRepository
{
    public function paginate(?string $search = null, int $perPage = 15): LengthAwarePaginator;

    public function create(array $data): Product;
}
