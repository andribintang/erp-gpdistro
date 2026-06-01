<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Warehouse;

class WarehousePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }

    public function update(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }

    public function delete(User $user, Warehouse $warehouse): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }
}
