<?php

namespace App\Policies;

use App\Models\Product;
use App\Models\User;

class ProductPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function update(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function delete(User $user, Product $product): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }
}
