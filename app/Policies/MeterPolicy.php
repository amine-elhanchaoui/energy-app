<?php

namespace App\Policies;

use App\Models\Meter;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MeterPolicy
{
    public function view(User $authUser, Meter $meter)
    {
        return $authUser->hasRole('admin') || $authUser->id === $meter->user_id;
    }

    public function create(User $authUser)
    {
        return $authUser->hasRole('admin') || $authUser->hasRole('citoyen');
    }

    public function update(User $authUser, Meter $meter)
    {
        return $authUser->hasRole('admin') || $authUser->id === $meter->user_id;
    }

    public function delete(User $authUser, Meter $meter)
    {
        return $authUser->hasRole('admin');
    }
}
