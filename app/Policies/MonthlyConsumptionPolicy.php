<?php

namespace App\Policies;

use App\Models\MonthlyConsuption;
use App\Models\User;
use Illuminate\Auth\Access\Response;

class MonthlyConsumptionPolicy
{
   public function view(User $authUser)
{
    return $authUser->hasRole('admin');
}
}
