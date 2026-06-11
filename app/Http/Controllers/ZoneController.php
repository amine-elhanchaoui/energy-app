<?php

namespace App\Http\Controllers;

use App\Models\City;
use App\Models\Quartier;
use Illuminate\Http\Request;

class ZoneController extends Controller
{
    // this methode will return all cities with the count of quartiers in each city
    public function cities()
    {
        $cities = City::withCount('quartiers')
            ->orderBy('name')
            ->get();

        return response()->json($cities);
    }

    public function storeCity(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $city = City::create($validated);

        return response()->json([
            'message' => 'City created successfully',
            'data' => $city,
        ], 201);
    }

    public function showCity($id)
    {
        $city = City::with('quartiers')->findOrFail($id);

        return response()->json($city);
    }

    public function updateCity(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
        ]);

        $city = City::findOrFail($id);
        $city->update($validated);

        return response()->json([
            'message' => 'City updated successfully',
            'data' => $city,
        ]);
    }

    public function destroyCity($id)
    {
        $city = City::findOrFail($id);
        $city->delete();

        return response()->json([
            'message' => 'City deleted successfully',
        ]);
    }

    public function quartiers(Request $request)
    {
        $request->validate([
            'city_id' => 'nullable|integer|exists:cities,id',
        ]);

        $quartiers = Quartier::with('city:id,name')
            ->when($request->city_id, function ($query, $cityId) {
                return $query->where('city_id', $cityId);
            })
            ->orderBy('name')
            ->get();

        return response()->json($quartiers);
    }

    public function storeQuartier(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|integer|exists:cities,id',
        ]);

        $quartier = Quartier::create($validated)->load('city:id,name');

        return response()->json([
            'message' => 'Quartier created successfully',
            'data' => $quartier,
        ], 201);
    }

    public function showQuartier($id)
    {
        $quartier = Quartier::with('city:id,name')->findOrFail($id);

        return response()->json($quartier);
    }

    public function updateQuartier(Request $request, $id)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'city_id' => 'required|integer|exists:cities,id',
        ]);

        $quartier = Quartier::findOrFail($id);
        $quartier->update($validated);
        $quartier->load('city:id,name');

        return response()->json([
            'message' => 'Quartier updated successfully',
            'data' => $quartier,
        ]);
    }

    public function destroyQuartier($id)
    {
        $quartier = Quartier::findOrFail($id);
        $quartier->delete();

        return response()->json([
            'message' => 'Quartier deleted successfully',
        ]);
    }

    public function AddQuartier(Request $request)
    {
        return $this->storeQuartier($request);
    }

    public function GetQuartiersByCity($cityId)
    {
        $request = new Request(['city_id' => $cityId]);

        return $this->quartiers($request);
    }

    public function GetAllQuartiers()
    {
        return $this->quartiers(new Request());
    }
}
