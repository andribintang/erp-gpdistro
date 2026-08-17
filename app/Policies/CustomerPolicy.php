<?php

namespace App\Policies;

use App\Models\Customer;
use App\Models\User;

class CustomerPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function update(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function delete(User $user, Customer $customer): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner']);
    }
}
