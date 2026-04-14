<?php

namespace App\Support;

use App\Models\User;

class RoleResolver
{
    public static function isAdmin(User $user): bool
    {
        return $user->hasRole('admin') || $user->hasRole('administrateur');
    }

    public static function isCitizen(User $user): bool
    {
        return $user->hasRole('citizen') || $user->hasRole('citoyen');
    }
}
