<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Models\Meter;
use App\Models\Reading;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Foundation\Auth\Access\AuthorizesRequests;
use Maatwebsite\Excel\Facades\Excel;
use App\Exports\ReadingsExport;
use Barryvdh\DomPDF\Facade\Pdf;

class AdminController extends Controller
{
    use AuthorizesRequests;

    // user management functions
    public function getAllUsers() {
        $this->authorize('view_global_dashboard');
        return response()->json(User::all());
    }

    public function createUser(Request $request) {
        $this->authorize('create', User::class);

        $validated = $request->validate([
            'name'=>'required|string|max:255',
            'email'=>'required|string|email|max:255|unique:users',
            'password'=>'required|string|min:8',
            'city_id'=>'nullable|exists:cities,id',
            'quartier'=>'required|string|max:255',
            'house_number'=>'required|string|max:255',
        ]);

        $user = User::create([
            'name'=>$validated['name'],
            'email'=>$validated['email'],
            'password'=>bcrypt($validated['password']),
            'city_id'=>$validated['city_id'] ?? null,
            'quartier'=>$validated['quartier'],
            'house_number'=>$validated['house_number'],
        ]);
        $user->assignRole('citoyen');
        return response()->json($user, 201);
    }

    public function updateUser(Request $request, $id) {
        $user = User::findOrFail($id);
        $this->authorize('update', $user);

        $validated = $request->validate([
            'name'=>'sometimes|required|string|max:255',
            'email'=>'sometimes|required|string|email|max:255|unique:users,email,'.$user->id,
            'password'=>'sometimes|required|string|min:8',
            'city_id'=>'nullable|exists:cities,id',
            'quartier'=>'sometimes|required|string|max:255',
            'house_number'=>'sometimes|required|string|max:255',
        ]);

        if(isset($validated['password'])){
            $validated['password'] = bcrypt($validated['password']);
        }

        $user->update($validated);
        return response()->json($user);
    }

    public function deleteUser($id) {
        $user = User::findOrFail($id);
        $this->authorize('delete', $user);
        $user->delete();
        return response()->json(['message'=>'User deleted']);
    }
    
    // toggle(changing) user active/inactive status
    public function toggleUserStatus($id){
        $user = User::findOrFail($id);
        $this->authorize('update', $user);
        $user->is_active = !$user->is_active;
        $user->save();
        return response()->json(['message'=>'User status updated', 'is_active'=>$user->is_active]);
    }

   // global statistics 
    public function GlobalStatistics() {
        $this->authorize('view_global_dashboard');

        $consumptionByCity = DB::table('readings')
            ->join('meters', 'readings.meter_id', '=', 'meters.id')
            ->join('quartiers', 'meters.quartier_id', '=', 'quartiers.id')
            ->join('cities', 'quartiers.city_id', '=', 'cities.id')
            ->groupBy('cities.id', 'cities.name')
            ->selectRaw('cities.name as city, SUM(readings.value) as total_consumption')
            ->get();

        $consumptionByQuartier = DB::table('readings')
            ->join('meters', 'readings.meter_id', '=', 'meters.id')
            ->join('quartiers', 'meters.quartier_id', '=', 'quartiers.id')
            ->join('cities', 'quartiers.city_id', '=', 'cities.id')
            ->groupBy('quartiers.id', 'quartiers.name', 'cities.name')
            ->selectRaw('CONCAT(cities.name, " - ", quartiers.name) as quartier, SUM(readings.value) as total_consumption')
            ->get();

        $stats = [
            'total_users'=>User::count(),
            'active_users'=>User::where('is_active', true)->count(),
            'inactive_users'=>User::where('is_active', false)->count(),
            'total_meters'=>Meter::count(),
            'total_readings'=>Reading::count(),
            'average_consumption'=>Reading::avg('value'),
            'total_consumption'=>Reading::sum('value'),
            'consumption_by_city'=>$consumptionByCity,
            'consumption_by_quartier'=>$consumptionByQuartier,
            'monthly_trends'=>DB::table('monthly_consumptions')
                ->selectRaw('MONTH(month) as month, SUM(consumption_value) as total_consumption')
                ->groupByRaw('MONTH(month)')
                ->orderByRaw('MONTH(month)')
                ->get(),
        ];

        return response()->json($stats);
    }

    // export readings to CSV and PDF
    public function exportReadingsCsv() {
        $this->authorize('view_global_dashboard');
        return Excel::download(new ReadingsExport, 'energy_readings_'.now()->format('Y_m_d_His').'.csv');
    }

    public function exportStatsPdf() {
        $this->authorize('view_global_dashboard');
        $data = Reading::with('meter.user')->latest()->limit(100)->get();
        $pdf = Pdf::loadView('exports.readings_pdf', ['readings'=>$data]);
        return $pdf->download('energy_report_'.now()->format('Y_m_d_His').'.pdf');
    }
}