<?php

namespace App\Services;

use App\Models\Meter;
use App\Models\MonthlyConsumption;
use App\Models\Reading;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class ReadingService
{
    /**
     * Create a reading and recalculate monthly consumption.
     */
    public function createReading(Request $request, array $validated): Reading
    {
        $meter = Meter::findOrFail($validated['meter_id']);

        $existingReading = Reading::where('meter_id', $validated['meter_id'])
            ->where('date', $validated['date'])
            ->first();

        $photoPath = $existingReading?->photo_path;
        if ($request->hasFile('photo')) {
            if ($existingReading?->photo_path) {
                Storage::disk('public')->delete($existingReading->photo_path);
            }
            $photoPath = $request->file('photo')->store('readings', 'public');
        }

        if ($existingReading) {
            $existingReading->update([
                'value' => $validated['value'],
                'photo_path' => $photoPath,
            ]);
            $reading = $existingReading->fresh();
        } else {
            $reading = Reading::create([
                'meter_id' => $validated['meter_id'],
                'date' => $validated['date'],
                'value' => $validated['value'],
                'photo_path' => $photoPath,
            ]);
        }

        $this->updateMonthlyConsumption($meter, $validated['date']);

        return $reading;
    }

    /**
     * Update a reading and recalculate monthly consumption.
     */
    public function updateReading(Request $request, Reading $reading, array $validated): Reading
    {
        $meter = Meter::findOrFail($reading->meter_id);

        if ($request->hasFile('photo')) {
            if ($reading->photo_path) {
                Storage::disk('public')->delete($reading->photo_path);
            }
            $validated['photo_path'] = $request->file('photo')->store('readings', 'public');
        }

        $oldDate = $reading->date;
        $reading->update($validated);

        if (isset($validated['date']) && $oldDate != $validated['date']) {
            $this->updateMonthlyConsumption($meter, $oldDate);
        }
        $this->updateMonthlyConsumption($meter, $reading->date);

        return $reading;
    }

    /**
     * Delete a reading and recalculate monthly consumption.
     */
    public function deleteReading(Reading $reading): void
    {
        $meter = Meter::findOrFail($reading->meter_id);

        if ($reading->photo_path) {
            Storage::disk('public')->delete($reading->photo_path);
        }

        $readingDate = $reading->date;
        $reading->delete();

        $this->updateMonthlyConsumption($meter, $readingDate);
    }

    /**
     * Recalculate monthly consumption for one meter and month.
     */
    public function updateMonthlyConsumption(Meter $meter, $date): void
    {
        $date = Carbon::parse($date);
        $month = $date->startOfMonth();

        $readings = Reading::where('meter_id', $meter->id)
            ->whereYear('date', $month->year)
            ->whereMonth('date', $month->month)
            ->orderBy('date')
            ->get();

        if ($readings->count() > 0) {
            $firstValue = $readings->first()->value;
            $lastValue = $readings->last()->value;
            $consumption = max(0, $lastValue - $firstValue);

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
            MonthlyConsumption::where('meter_id', $meter->id)
                ->where('month', $month)
                ->delete();
        }
    }
}
