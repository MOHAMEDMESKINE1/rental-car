<?php

namespace App\Policies;

use App\Models\User;
use App\Models\Vehicle;

class VehiclePolicy
{
    public function viewAny(User $user): bool
    {
        return $user->can('view vehicles');
    }

    public function view(User $user, Vehicle $vehicle): bool
    {
        return $user->can('view vehicles');
    }

    public function create(User $user): bool
    {
        return $user->can('create vehicles');
    }

    public function update(User $user, Vehicle $vehicle): bool
    {
        return $user->can('edit vehicles');
    }

    public function delete(User $user, Vehicle $vehicle): bool
    {
        return $user->can('delete vehicles');
    }
}
