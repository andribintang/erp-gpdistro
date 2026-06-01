<?php

namespace App\Policies;

use App\Models\Inventory;
use App\Models\User;

class InventoryPolicy
{
    public function viewAny(User $user): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }

    public function adjust(User $user, Inventory $inventory): bool
    {
        return $user->hasAnyRole(['Super Admin', 'Owner', 'Manager']);
    }
}
