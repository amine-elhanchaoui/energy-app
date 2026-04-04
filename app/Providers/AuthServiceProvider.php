<?php

namespace App\Providers;

use Illuminate\Foundation\Support\Providers\AuthServiceProvider as ServiceProvider;

class AuthServiceProvider extends ServiceProvider
{
    /**
     * Register services.
     */

    protected $policies = [
        // 'App\Models\Model' => 'App\Policies\ModelPolicy',
        'App\Models\User' => 'App\Policies\UserPolicy',
        'App\Models\MonthlyConsumption' => 'App\Policies\MonthlyConsumptionPolicy',
        'App\Models\Meter' => 'App\Policies\MeterPolicy',
        'App\Models\Reading' => 'App\Policies\ReadingPolicy',
    ];
    public function register(): void
    {
        //
        
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        //
            $this->registerPolicies();
    }
}
