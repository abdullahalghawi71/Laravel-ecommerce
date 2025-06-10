<?php

namespace App\Policies;

use App\Models\Supplier;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class SupplierPolicy
{
    public function create(User $user): bool
    {
        return $user->role === 'manager';
    }

    public function update(User $user, Supplier $supplier): bool
    {
        return $user->role === 'manager';
    }

    public function delete(User $user, Supplier $supplier): bool
    {
        return $user->role === 'manager';
    }
}
