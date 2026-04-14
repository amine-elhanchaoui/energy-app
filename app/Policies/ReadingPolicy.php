<?php

namespace App\Policies;

use App\Models\Reading;
use App\Models\User;
use App\Support\RoleResolver;
use Illuminate\Auth\Access\Response;

class ReadingPolicy
{
    private function isAdmin(User $authUser): bool
    {
        return RoleResolver::isAdmin($authUser);
    }

   public function view(User $authUser, Reading $reading)
{
    return $this->isAdmin($authUser) || $authUser->id === $reading->meter->user_id;
}

public function create(User $authUser)
{
    return $this->isAdmin($authUser) || RoleResolver::isCitizen($authUser);
}

public function update(User $authUser, Reading $reading)
{
    return $this->isAdmin($authUser) || $authUser->id === $reading->meter->user_id;
}

public function delete(User $authUser, Reading $reading)
{
    return $this->isAdmin($authUser) || $authUser->id === $reading->meter->user_id;
}
}
