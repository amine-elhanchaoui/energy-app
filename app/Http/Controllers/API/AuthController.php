<?php

namespace App\Http\Controllers\API;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Quartier;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\ValidationException;
use App\Models\City;

class AuthController extends Controller
{
    
    public function register(Request $request)
    {
        
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users',
            'password' => 'required|string|min:6|confirmed',
            'quartier' => 'required|string|max:255',
            'house_number' => 'required|string|max:255',
            'city_id' => 'nullable|exists:cities,id',
        ]);


        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'quartier' => $request->quartier,
            'house_number' => $request->house_number,
            'city_id' => $request->city_id ?? null,
        ]);

        // give the user the "citoyen" role by default (French spelling from seeder)
        $user->assignRole('citoyen');

       
        $token = $user->createToken('auth_token')->plainTextToken;

        
        return response()->json([
            'success' => true,
            'message' => 'Registration successful',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function login(Request $request)
    {
        // validation of data
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        // Search for the user by email
        $user = User::where('email', $request->email)->first();

        // verification of user and password
        if (!$user || !Hash::check($request->password, $user->password)) {
            throw ValidationException::withMessages([
                'email' => ['email or password is incorrect'],
            ]);
        }

        // delete old token
        $user->tokens()->delete();

        // create new token
        $token = $user->createToken('auth_token')->plainTextToken;

        // token with user details and permissions
        return response()->json([
            'success' => true,
            'message' => 'login successfully',
            'user' => [
                'id' => $user->id,
                'name' => $user->name,
                'email' => $user->email,
                'roles' => $user->getRoleNames(),
                'permissions' => $user->getAllPermissions()->pluck('name'),
            ],
            'token' => $token,
            'token_type' => 'Bearer',
        ]);
    }

   //logout
    public function logout(Request $request)
    {
        
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'success' => true,
            'message' => 'logged out successfully',
        ]);
    }

  //get authenticated user details
    public function user(Request $request)
    {
        $user = $request->user();
        
        return response()->json([
            'id' => $user->id,
            'name' => $user->name,
            'email' => $user->email,
            'address' => $user->address,
            'roles' => $user->getRoleNames(),
            'permissions' => $user->getAllPermissions()->pluck('name'),
        ]);
    }

   //change password
    public function changePassword(Request $request)
    {
        $request->validate([
            'current_password' => 'required',
            'new_password' => 'required|min:6|confirmed',
        ]);

        $user = $request->user();

        if (!Hash::check($request->current_password, $user->password)) {
            return response()->json([
                'success' => false,
                'message' => 'passwords do not match',
            ], 422);
        }

        $user->password = Hash::make($request->new_password);
        $user->save();

        return response()->json([
            'success' => true,
            'message' => 'successfully changed password',
        ]);
    }

    public function getCities(){
        $cities = City::all();
        return response()->json($cities);
    }

    public function getQuartiers(Request $request){
        $request->validate([
            'city_id' => 'nullable|exists:cities,id',
        ]);
        // when method means that if the city_id is present in the request, we will filter the quartiers by that city_id, otherwise we will return all the quartiers,
        // this way we can use the same endpoint to get all the quartiers or to get the quartiers of a specific city, depending on whether the city_id is provided in the request or not.
        $quartiers = Quartier::when($request->city_id, function ($query, $cityId) {
            return $query->where('city_id', $cityId);
        })->get();

        return response()->json($quartiers);
    }
}
