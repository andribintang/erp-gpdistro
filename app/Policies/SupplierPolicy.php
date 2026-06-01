<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;

class SupplierPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }
}
