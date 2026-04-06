<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\Reading;
use App\Models\Meter;
use App\Models\MonthlyConsumption;
use Illuminate\Http\Request;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Illuminate\Support\Facades\Auth;
use Carbon\Carbon;
use Illuminate\Support\Facades\Storage;

class ReadingController extends Controller
{
    use AuthorizesRequests;

    /**
     * Get all readings (filtered by user's meters if citizen)
     */
    public function index()
    {
        $user = Auth::user();
        
        if ($user->hasRole('admin')) {
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
        $meter = Meter::findOrFail($reading->meter_id);
        $this->authorize('update', $reading);

        $validated = $request->validate([
            // sometimes means that the field is optional
            'date' => 'sometimes|required|date',
            'value' => 'sometimes|required|numeric|min:0',
            'photo' => 'nullable|image|mimes:jpeg,png,jpg,gif|max:2048',
        ]);

        // Handle photo upload
        if ($request->hasFile('photo')) {
            // Delete old photo if exists
            if ($reading->photo_path) {
                // storage::disk('public') is used to access the public disk defined in the filesystems.php configuration file, and delete method is used to delete the file at the specified path. This ensures that when a new photo is uploaded for a reading, the old photo is removed from storage to free up space and avoid orphaned files.
                Storage::disk('public')->delete($reading->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('readings', 'public');
        }

        $oldDate = $reading->date;
        $reading->update($validated);

        // Recalculate monthly consumption for both old and new dates
        if (isset($validated['date']) && $oldDate != $validated['date']) {
            // we recalculate the monthly consumption for the old date to ensure that if the reading's date was changed, we update the consumption for the month of the old date as well, since it might affect the consumption calculation for that month.
            $this->updateMonthlyConsumption($meter, $oldDate);
        }
        // automatically calculate monthly consumption for the new date (or the same date if it wasn't changed) !!this function is below in the same controller for this reason we use "$this->updateMonthlyConsumption" to call it!!
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
        $this->authorize('delete', $reading);

        // Delete photo if exists
        if ($reading->photo_path) {
            Storage::disk('public')->delete($reading->photo_path);
        }

        $readingDate = $reading->date;
        $reading->delete();

        // 
        $this->updateMonthlyConsumption($meter, $readingDate);

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
            // we order the readings by date to make a cronoloical order to be able to calculate the consumption as the difference between the last and first reading of the month.
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
