<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\Quartier;
use App\Models\User;
use App\Support\RoleResolver;
use Illuminate\Support\Collection;
use Illuminate\Validation\ValidationException;

class MeterService
{
    /**
     * Build query for meters based on role.
     */
    public function meterQueryForUser(User $user): \Illuminate\Database\Eloquent\Builder
    {
        $query = Meter::query();

        if (!RoleResolver::isAdmin($user)) {
            $query->where('user_id', $user->id);
        }

        return $query;
    }

    /**
     * Create meter with safe user/quartier handling.
     */
    public function createMeter(array $validated, User $authUser): Meter
    {
        if (!RoleResolver::isAdmin($authUser)) {
            $validated['user_id'] = $authUser->id;
        } elseif (!isset($validated['user_id'])) {
            $validated['user_id'] = $authUser->id;
        }

        if (!isset($validated['quartier_id'])) {
            $quartier = Quartier::where('name', $authUser->quartier)
                ->when($authUser->city_id, function ($query, $cityId) {
                    return $query->where('city_id', $cityId);
                })
                ->first();

            if (!$quartier) {
                throw ValidationException::withMessages([
                    'quartier_id' => ['quartier_id is required for this account. Please update your profile quartier first.'],
                ]);
            }

            $validated['quartier_id'] = $quartier->id;
        }

        return Meter::create($validated);
    }

    /**
     * Format meters list for /api/my-meters.
     */
    public function formatUserMeters(Collection $meters): Collection
    {
        return $meters->map(function ($meter) {
            return [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
                'location' => $meter->location,
                'user_id' => $meter->user_id,
                'latest_reading' => $meter->readings->first()?->value,
                'latest_reading_date' => $meter->readings->first()?->date,
                'total_readings' => $meter->readings->count(),
            ];
        });
    }

    /**
     * Format meters list for dashboard chart endpoint.
     */
    public function formatMetersWithReadings(Collection $meters): Collection
    {
        return $meters->map(function ($meter) {
            return [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
                'location' => $meter->location,
                'user_id' => $meter->user_id,
                'readings_count' => $meter->readings->count(),
                'last_reading' => [
                    'value' => $meter->readings->first()?->value,
                    'date' => $meter->readings->first()?->date,
                ],
                'monthly_data' => $meter->monthlyConsumptions->map(function ($mc) {
                    return [
                        'month' => $mc->month,
                        'value' => $mc->consumption_value,
                    ];
                }),
            ];
        });
    }
}
