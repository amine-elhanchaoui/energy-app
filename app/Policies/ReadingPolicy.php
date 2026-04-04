<?php

namespace App\Policies;

use App\Models\Reading;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class ReadingPolicy
{
   public function view(User $authUser, Reading $reading)
{
    return $authUser->hasRole('admin') || $authUser->id === $reading->meter->user_id;
}

public function create(User $authUser)
{
    return $authUser->hasRole('admin') || $authUser->hasRole('citoyen');
}

public function update(User $authUser, Reading $reading)
{
    return $authUser->hasRole('admin') || $authUser->id === $reading->meter->user_id;
}

public function delete(User $authUser, Reading $reading)
{
    return $authUser->hasRole('admin');
}
}
