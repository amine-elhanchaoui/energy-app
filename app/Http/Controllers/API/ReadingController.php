<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reading;
use App\Models\Meter;
use App\Services\ReadingService;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use App\Support\RoleResolver;

class ReadingController extends Controller
{
    use AuthorizesRequests;

    public function __construct(private ReadingService $readingService)
    {
    }

    private function isAdmin($user): bool
    {
        return RoleResolver::isAdmin($user);
    }

    /**
     * Get all readings (filtered by user's meters if citizen)
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($this->isAdmin($user)) {
            $readings = Reading::with(['meter.user', 'meter.quartier'])
                ->latest('date')
                ->paginate(50);
        } else {
            // Get readings only from user's meters
            $readings = Reading::whereIn('meter_id', 
            // pluck methode is used to get an array of meter ids that belong to the user, and then we use whereIn to filter readings that have a meter_id in that array.
                Meter::where('user_id', $user->id)->pluck('id')
            )
            ->with(['meter.user', 'meter.quartier'])
            ->latest('date')
            ->paginate(50);
        }

        return response()->json($readings);
    }

    /**
     * Get a specific reading
     */
    public function show($id)
    {
        // meter.user means that we want to load the user relationship of the meter, and meter.quartier means that we want to load the quartier relationship of the meter. This way we can return all the necessary information about the reading, including the user and quartier associated with the meter.
        $reading = Reading::with(['meter.user', 'meter.quartier'])->findOrFail($id);
        
        $meter = Meter::findOrFail($reading->meter_id);
        $this->authorize('view', $meter);

        return response()->json($reading);
    }

    /**
     * Create a new reading
     */
    public function store(Request $request)
    {
        $this->authorize('create', Reading::class);

        $validated = $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'date' => 'required|date',
            'value' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $meter = Meter::findOrFail($validated['meter_id']);
        $this->authorize('view', $meter);
        $reading = $this->readingService->createReading($request, $validated);

        return response()->json(
            // we use load here tp get the related user and quartier data for the meter after creating the reading, so that we can return all the necessary information in the response.
            $reading->load(['meter.user', 'meter.quartier']),
            201
        );
    }

    /**
     * Update a reading
     */
    public function update(Request $request, $id)
    {
        $reading = Reading::findOrFail($id);
        $this->authorize('update', $reading);

        $validated = $request->validate([
            // sometimes means that the field is optional
            'date' => 'sometimes|required|date',
            'value' => 'sometimes|required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $reading = $this->readingService->updateReading($request, $reading, $validated);

        return response()->json(
            $reading->load(['meter.user', 'meter.quartier'])
        );
    }

    /**
     * Delete a reading
     */
    public function destroy($id)
    {
        $reading = Reading::findOrFail($id);
        $this->authorize('delete', $reading);
        $this->readingService->deleteReading($reading);

        return response()->json(['message' => 'Reading deleted successfully']);
    }

    /**
     * Get readings for a specific meter
     */

    // the role of this methode in the frontend is to get all the readings for a specific meter, and we will use it in the meter details page to show the list of readings for that meter, and also to show the chart of readings for that meter.
    public function getMeterReadings($meterId)
    {
        $meter = Meter::findOrFail($meterId);
        $this->authorize('view', $meter);

        $readings = Reading::where('meter_id', $meterId)
            ->latest('date')
            ->get();

        return response()->json([
            'meter' => [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
            ],
            'readings' => $readings,
        ]);
    }

    /**
     * Get readings for a date range
     */
    public function getReadingsByDateRange(Request $request, $meterId)
    {
        $meter = Meter::findOrFail($meterId);
        $this->authorize('view', $meter);

        // from frontend we will send start_date and end_date as query parameters.
        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

        // get the reading for the specified meter and date range, ordered by date in descending order (latest first)
        $readings = Reading::where('meter_id', $meterId)
            ->whereBetween('date', [$validated['start_date'], $validated['end_date']])
            ->latest('date')
            ->get();

        return response()->json([
            'meter' => [
                'id' => $meter->id,
                'name' => $meter->name,
                'type' => $meter->type,
                'unit' => $meter->unit,
            ],
            'date_range' => [
                'start' => $validated['start_date'],
                'end' => $validated['end_date'],
            ],
            'readings' => $readings,
        ]);
    }

}
