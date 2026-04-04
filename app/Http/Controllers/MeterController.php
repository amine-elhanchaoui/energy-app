<?php

namespace App\Http\Controllers;

use App\Models\Meter;
use App\Models\Reading;
use App\Models\MonthlyConsumption;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

class MeterController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all meters (filtered by user if citizen)
     */
    public function index()
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $meters = Meter::with([
                'user:id,name,email',
                'quartier:id,name',
                'readings',
                'monthlyConsumptions'
            ])->get();
        } else {
            $meters = Meter::where('user_id', $user->id)
                ->with([
                    'user:id,name,email',
                    'quartier:id,name',
                    'readings',
                    'monthlyConsumptions'
                ])->get();
        }

        return response()->json($meters);
    }

    /**
     * Get authenticated user's meters
     */
    public function getUserMeters()
    {
        $user = auth()->user();
        
        $meters = Meter::where('user_id', $user->id)
            ->with([
                'readings' => function ($query) {
                    $query->latest('date')->limit(5);
                },
                'monthlyConsumptions'
            ])
            ->get()
            ->map(function ($meter) {
                return [
                    'id' => $meter->id,
                    'name' => $meter->name,
                    'type' => $meter->type,
                    'unit' => $meter->unit,
                    'location' => $meter->location,
                    'latest_reading' => $meter->readings->first()?->value,
                    'latest_reading_date' => $meter->readings->first()?->date,
                    'total_readings' => $meter->readings->count(),
                ];
            });

        return response()->json($meters);
    }

    /**
     * Get a specific meter with all relationships
     */
    public function show($id)
    {
        $meter = Meter::with([
            'user:id,name,email,city_id,quartier,house_number',
            'quartier:id,name',
            'readings' => function ($query) {
                $query->latest('date');
            },
            'monthlyConsumptions'
        ])->findOrFail($id);

        $this->authorize('view', $meter);

        return response()->json($meter);
    }

    /**
     * Create a new meter
     */
    public function store(Request $request)
    {
        $this->authorize('create', Meter::class);

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'type' => 'required|in:electricity,gas,water',
            'unit' => 'required|in:kWh,m³,liters',
            'location' => 'nullable|string|max:255',
            'quartier_id' => 'required|exists:quartiers,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        // If user_id not provided, use authenticated user's ID (for citizens)
        if (!isset($validated['user_id'])) {
            $validated['user_id'] = auth()->id();
        }

        $meter = Meter::create($validated);

        return response()->json(
            $meter->load(['user:id,name,email', 'quartier:id,name']),
            201
        );
    }

    /**
     * Update a meter
     */
    public function update(Request $request, $id)
    {
        $meter = Meter::findOrFail($id);
        $this->authorize('update', $meter);

        $validated = $request->validate([
            'name' => 'sometimes|required|string|max:255',
            'type' => 'sometimes|required|in:electricity,gas,water',
            'unit' => 'sometimes|required|in:kWh,m³,liters',
            'location' => 'nullable|string|max:255',
            'quartier_id' => 'sometimes|required|exists:quartiers,id',
        ]);

        $meter->update($validated);

        return response()->json(
            $meter->load(['user:id,name,email', 'quartier:id,name'])
        );
    }

    /**
     * Delete a meter
     */
    public function destroy($id)
    {
        $meter = Meter::findOrFail($id);
        $this->authorize('delete', $meter);

        // Delete related data
        $meter->readings()->delete();
        $meter->monthlyConsumptions()->delete();
        $meter->delete();

        return response()->json(['message' => 'Meter deleted successfully']);
    }

    /**
     * Get monthly consumption data for a meter
     */
    public function getMonthlyData($meterId)
    {
        $meter = Meter::findOrFail($meterId);
        $this->authorize('view', $meter);

        $monthlyData = MonthlyConsumption::where('meter_id', $meterId)
            ->select('month', 'consumption_value')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            ->reverse();

        return response()->json([
            'meter' => [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
            ],
            'data' => $monthlyData,
        ]);
    }

    /**
     * Compare current month with previous month
     */
    public function compareMonths($meterId)
    {
        $meter = Meter::findOrFail($meterId);
        $this->authorize('view', $meter);

        $currentDate = Carbon::now();
        $previousDate = $currentDate->copy()->subMonth();

        $currentMonth = MonthlyConsumption::where('meter_id', $meterId)
            ->whereYear('month', $currentDate->year)
            ->whereMonth('month', $currentDate->month)
            ->first();

        $previousMonth = MonthlyConsumption::where('meter_id', $meterId)
            ->whereYear('month', $previousDate->year)
            ->whereMonth('month', $previousDate->month)
            ->first();

        $currentValue = $currentMonth?->consumption_value ?? 0;
        $previousValue = $previousMonth?->consumption_value ?? 0;

        $difference = $currentValue - $previousValue;
        $percentageChange = $previousValue > 0 
            ? (($difference / $previousValue) * 100) 
            : 0;

        return response()->json([
            'meter' => [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
            ],
            'current_month' => [
                'month' => $currentDate->format('Y-m'),
                'value' => $currentValue,
            ],
            'previous_month' => [
                'month' => $previousDate->format('Y-m'),
                'value' => $previousValue,
            ],
            'comparison' => [
                'difference' => $difference,
                'percentage_change' => round($percentageChange, 2),
                'trend' => $difference > 0 ? 'increased' : ($difference < 0 ? 'decreased' : 'stable'),
            ],
        ]);
    }

    /**
     * Get average consumption for comparison
     */
    public function getAverageComparison($meterId)
    {
        $meter = Meter::findOrFail($meterId);
        $this->authorize('view', $meter);

        // Get current month consumption
        $currentDate = Carbon::now();
        $currentMonth = MonthlyConsumption::where('meter_id', $meterId)
            ->whereYear('month', $currentDate->year)
            ->whereMonth('month', $currentDate->month)
            ->first();

        $currentValue = $currentMonth?->consumption_value ?? 0;

        // Get average of same type meters for current month
        $averageConsumption = MonthlyConsumption::join('meters', 'monthly_consumptions.meter_id', '=', 'meters.id')
            ->where('meters.type', $meter->type)
            ->whereYear('monthly_consumptions.month', $currentDate->year)
            ->whereMonth('monthly_consumptions.month', $currentDate->month)
            ->avg('monthly_consumptions.consumption_value');

        $averageConsumption = $averageConsumption ?? 0;
        $difference = $currentValue - $averageConsumption;
        $percentageFromAverage = $averageConsumption > 0 
            ? (($difference / $averageConsumption) * 100) 
            : 0;

        return response()->json([
            'meter' => [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
            ],
            'user_consumption' => $currentValue,
            'average_consumption' => round($averageConsumption, 2),
            'difference' => round($difference, 2),
            'percentage_from_average' => round($percentageFromAverage, 2),
            'status' => $difference > 0 ? 'above_average' : 'below_average',
        ]);
    }

    /**
     * Get meters with their readings for dashboard
     */
    public function getMetersWithReadings()
    {
        $user = auth()->user();

        $meters = Meter::where('user_id', $user->id)
            ->with([
                'readings' => function ($query) {
                    $query->latest('date')->limit(10);
                },
                'monthlyConsumptions' => function ($query) {
                    $query->latest('month')->limit(6);
                }
            ])
            ->get()
            ->map(function ($meter) {
                return [
                    'id' => $meter->id,
                    'name' => $meter->name,
                    'type' => $meter->type,
                    'unit' => $meter->unit,
                    'location' => $meter->location,
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

        return response()->json($meters);
    }

    /**
     * Get consumption statistics for admin dashboard
     */
    public function getConsumptionStatistics()
    {
        auth()->user()->hasRole('admin') || abort(403);

        $currentDate = Carbon::now();
        $previousDate = $currentDate->copy()->subMonth();

        $stats = [
            'total_meters' => Meter::count(),
            'meters_by_type' => Meter::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            'current_month_consumption' => MonthlyConsumption::whereYear('month', $currentDate->year)
                ->whereMonth('month', $currentDate->month)
                ->sum('consumption_value'),
            'previous_month_consumption' => MonthlyConsumption::whereYear('month', $previousDate->year)
                ->whereMonth('month', $previousDate->month)
                ->sum('consumption_value'),
            'average_monthly_consumption' => MonthlyConsumption::avg('consumption_value'),
            'consumption_by_type' => MonthlyConsumption::join('meters', 'monthly_consumptions.meter_id', '=', 'meters.id')
                ->whereYear('monthly_consumptions.month', $currentDate->year)
                ->whereMonth('monthly_consumptions.month', $currentDate->month)
                ->selectRaw('meters.type, SUM(monthly_consumptions.consumption_value) as total')
                ->groupBy('meters.type')
                ->get(),
        ];

        return response()->json($stats);
    }
}
