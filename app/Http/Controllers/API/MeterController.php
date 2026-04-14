<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Meter;
use App\Models\Reading;
use App\Models\MonthlyConsumption;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use App\Support\RoleResolver;
use App\Services\MeterService;

class MeterController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private MeterService $meterService)
    {
    }

    private function isAdmin($user): bool
    {
        return RoleResolver::isAdmin($user);
    }

    /**
     * Get all meters (filtered by user if citizen)
     */
    public function index()
    {
        $user = Auth::user();

        if ($this->isAdmin($user)) {
            // get all meters with user and quartier info, and count of readings and monthly consumptions
            $meters = Meter::with([
                'user:id,name,email',
                'quartier:id,name',
                'readings',
                'monthlyConsumptions'
            ])->get();
        } else {
            // get only user's meters with user and quartier info, and count of readings and monthly consumptions
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
        $user = Auth::user();

        $meters = $this->meterService->meterQueryForUser($user)
            ->with([
                // we using this way because meter has many readings, and we want to get only the latest reading for each meter, so we can show it in the dashboard without loading all readings which can be heavy.
                'readings' => function ($query) {
                    // $query means the relationship query for readings, we can customize it to get only latest readings or count, etc. 
                    // skiping N+1 problem by eager loading and limiting readings to latest 5 for performance
                    // N+1 problem is when we load meters and then for each meter we load readings in a loop, which causes multiple queries. By using eager loading with constraints, we can load all necessary data in fewer queries.
                    $query
                        // order by date descending to get latest readings first
                        ->latest('date')
                        // limit to last 5 readings for performance
                        ->limit(5);

                    // this syntax returns only the latest 5 readings for each meter, and it is done in a single query using eager loading, which is more efficient than loading all readings and then slicing them in PHP.
                },
                'monthlyConsumptions'
            ])
            ->get();

        $meters = $this->meterService->formatUserMeters($meters);

        return response()->json($meters);

        /*
            Example response for getUserMeters() with latest reading and count of readings:
                           [
                 {
                   "id": 1,
                   "name": "Compteur Électricité",
                   "type": "electricity",
                   "unit": "kWh",
                   "location": "Salon",
                   "latest_reading": 125.5,
                   "latest_reading_date": "2026-04-01",
                   "total_readings": 5
                 },
                 {
                   "id": 2,
                   "name": "Compteur Eau",
                   "type": "water",
                   "unit": "m³",
                   "location": "Cuisine",
                   "latest_reading": 30.2,
                   "latest_reading_date": "2026-04-03",
                   "total_readings": 5
                 }
                        ]
        */
    }

    /**
     * Get a specific meter with all relationships
     */
    public function show($id)
    {
        $meter = Meter::with([
            // like select(id,name,email,city_id,quartier,house_number) from user rlationship
            'user:id,name,email,city_id,quartier,house_number',
            // like select(id,name) from quartier relationship
            'quartier:id,name',
            // load all readings for this meter, ordered by date descending
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
            'quartier_id' => 'nullable|exists:quartiers,id',
            'user_id' => 'sometimes|exists:users,id',
        ]);

        $meter = $this->meterService->createMeter($validated, Auth::user());

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


        // Get last 12 months of data for the meter, ordered by month descending
        $monthlyData = MonthlyConsumption::where('meter_id', $meterId)
            // this select means we only want the month and consumption_value columns from the monthly_consumptions table.
            ->select('month', 'consumption_value')
            ->orderBy('month', 'desc')
            ->limit(12)
            ->get()
            // reverse the collection to have it in ascending order by month (oldest first)
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

        // we use copy method to create a new instance of Carbon with the same date, so we can modify it without affecting the original $currentDate variable. This is important because we need to use $currentDate later to get the current month data, and if we modify it directly, it will affect that query as well.
        // subMonth method subtracts one month from the date, so we get the previous month date. For example, if currentDate is 2026-04-15, then previousDate will be 2026-03-15. We only care about the year and month part of the date for our queries, so the day part does not matter.
        $previousDate = $currentDate->copy()->subMonth();
        



         

        // whereYear = Year(month) and month column in databse is a complete date (like 2026-04-01) but we only care about the year and month part, so we use whereYear and whereMonth to filter by year and month separately. This way we can get the data for the current month and previous month without worrying about the day part of the date.
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
                // increased(positive difference), decreased(negative difference), or stable(zero difference)
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
                // here we care about the type of the meter, because we want to compare with similar meters, for example if this meter is electricity, we want to compare with other electricity meters, not water or gas meters, because they have different units and consumption patterns.
            ->where('meters.type', $meter->type)
            ->whereYear('monthly_consumptions.month', $currentDate->year)
            ->whereMonth('monthly_consumptions.month', $currentDate->month)
            ->avg('monthly_consumptions.consumption_value');
            // this query joins the monthly_consumptions table with the meters table to filter by meter type, and then it calculates the average consumption value for the current month for all meters of the same type.

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
        $user = Auth::user();
        $meters = $this->meterService->meterQueryForUser($user)
            ->with([
                'readings' => function ($query) {
                    $query->latest('date')->limit(10);
                },
                'monthlyConsumptions' => function ($query) {
                    $query->latest('month')->limit(6);
                }
            ])
            ->get();

        $meters = $this->meterService->formatMetersWithReadings($meters);

        return response()->json($meters);
    }

    /**
     * Get consumption statistics for admin dashboard
     */
    public function getConsumptionStatistics()
    {
        $user = Auth::user();
        $this->isAdmin($user) || abort(403);

        $currentDate = Carbon::now();
        $previousDate = $currentDate->copy()->subMonth();

        $stats = [
            'total_meters' => Meter::count(),
            'meters_by_type' => Meter::selectRaw('type, COUNT(*) as count')
                ->groupBy('type')
                ->get(),
            // total consumption for all types for current month
            'current_month_consumption' => MonthlyConsumption::whereYear('month', $currentDate->year)
                ->whereMonth('month', $currentDate->month)
                ->sum('consumption_value'),
            // this also but for previous month
            'previous_month_consumption' => MonthlyConsumption::whereYear('month', $previousDate->year)
                ->whereMonth('month', $previousDate->month)
                ->sum('consumption_value'),
            
            'average_monthly_consumption' => MonthlyConsumption::avg('consumption_value'),
            // total consumption by type for current month
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
