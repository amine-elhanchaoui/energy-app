<?php

namespace App\Policies;

use App\Models\Meter;
use App\Models\User;
use App\Support\RoleResolver;
use Illuminate\Auth\Access\Response;

class MeterPolicy
{
    private function isAdmin(User $authUser): bool
    {
        return RoleResolver::isAdmin($authUser);
    }

    public function view(User $authUser, Meter $meter)
    {
        return $this->isAdmin($authUser) || $authUser->id === $meter->user_id;
    }

    public function create(User $authUser)
    {
        return $this->isAdmin($authUser) || RoleResolver::isCitizen($authUser);
    }

    public function update(User $authUser, Meter $meter)
    {
        return $this->isAdmin($authUser) || $authUser->id === $meter->user_id;
    }

    public function delete(User $authUser, Meter $meter)
    {
        return $this->isAdmin($authUser) || $authUser->id === $meter->user_id;
    }
}
