<?php

namespace App\Policies;

use App\Models\Order;
use App\Models\User;

class OrderPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function create(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function update(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function process(User $user, Order $order): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }
}
