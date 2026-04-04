<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reading;
use App\Models\Meter;
use App\Models\MonthlyConsumption;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReadingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all readings (filtered by user's meters if citizen)
     */
    public function index(Request $request)
    {
        $user = auth()->user();
        
        if ($user->hasRole('admin')) {
            $readings = Reading::with(['meter.user', 'meter.quartier'])
                ->latest('date')
                ->paginate(50);
        } else {
            // Get readings only from user's meters
            $readings = Reading::whereIn('meter_id', 
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
        $validated = $request->validate([
            'meter_id' => 'required|exists:meters,id',
            'date' => 'required|date',
            'value' => 'required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        $meter = Meter::findOrFail($validated['meter_id']);
        $this->authorize('view', $meter);

        // Handle photo upload
        $photoPath = null;
        if ($request->hasFile('photo')) {
            $photoPath = $request->file('photo')->store('readings', 'public');
        }

        $reading = Reading::create([
            'meter_id' => $validated['meter_id'],
            'date' => $validated['date'],
            'value' => $validated['value'],
            'photo_path' => $photoPath,
        ]);

        // Automatically calculate monthly consumption
        $this->updateMonthlyConsumption($meter, $validated['date']);

        return response()->json(
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
        $meter = Meter::findOrFail($reading->meter_id);
        $this->authorize('view', $meter);

        $validated = $request->validate([
            'date' => 'sometimes|required|date',
            'value' => 'sometimes|required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($reading->photo_path) {
                Storage::disk('public')->delete($reading->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('readings', 'public');
        }

        $oldDate = $reading->date;
        $reading->update($validated);

        // Recalculate monthly consumption for both old and new dates
        if (isset($validated['date']) && $oldDate != $validated['date']) {
            $this->updateMonthlyConsumption($meter, $oldDate);
        }
        $this->updateMonthlyConsumption($meter, $reading->date);

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
        $meter = Meter::findOrFail($reading->meter_id);
        $this->authorize('view', $meter);

        // Delete photo if exists
        if ($reading->photo_path) {
            Storage::disk('public')->delete($reading->photo_path);
        }

        $readingDate = $reading->date;
        $reading->delete();

        // Recalculate monthly consumption
        $this->updateMonthlyConsumption($meter, $readingDate);

        return response()->json(['message' => 'Reading deleted successfully']);
    }

    /**
     * Get readings for a specific meter
     */
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

        $validated = $request->validate([
            'start_date' => 'required|date',
            'end_date' => 'required|date|after_or_equal:start_date',
        ]);

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

    /**
     * Update monthly consumption for a meter
     * This method calculates the total consumption for a given month
     */
    private function updateMonthlyConsumption(Meter $meter, $date)
    {
        $date = Carbon::parse($date);
        $month = $date->startOfMonth();

        // Get all readings for this month
        $readings = Reading::where('meter_id', $meter->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->orderBy('date')
            ->get();

        if ($readings->count() > 0) {
            // Calculate consumption as difference between last and first reading
            $firstValue = $readings->first()->value;
            $lastValue = $readings->last()->value;
            $consumption = $lastValue - $firstValue;

            // Ensure consumption is not negative
            $consumption = max(0, $consumption);

            // Update or create monthly consumption record
            MonthlyConsumption::updateOrCreate(
                [
                    'meter_id' => $meter->id,
                    'month' => $month,
                ],
                [
                    'consumption_value' => $consumption,
                ]
            );
        } else {
            // Delete monthly consumption if no readings exist
            MonthlyConsumption::where('meter_id', $meter->id)
                ->where('month', $month)
                ->delete();
        }
    }
}
