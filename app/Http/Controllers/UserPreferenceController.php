<?php

namespace App\Http\Controllers;

use App\Models\UserPreference;
use Illuminate\Http\Request;
use Illuminate\Http\Resources\Json\JsonResource;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\DB;

class UserPreferenceController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index(): UserPreference
    {
        return UserPreference::all();
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request): JsonResource
    {
        $validatedData = $request->validate([
            'user_id' => 'required|integer|unique:user_preferences,user_id',
            'notice_method' => 'required|in:SMS,E-mail,Both',
            'city' => 'required|string|max:255',
            'meteorological_warnings' => 'boolean',
            'hydrological_warnings' => 'boolean',
            'air_quality_warnings' => 'boolean',
            'temperature_warning' => 'boolean',
            'temperature_check_value' => 'numeric|max:50|min:-50',
        ]);
        $userPreference = UserPreference::create($validatedData);

        return response()->json($userPreference, 201);
    }

    public function showCurrentUserPreferences(Request $request): Response
    {
        return response([
            'preferences' => $request->user()->preference
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id): JsonResource
    {
        $userPreference = UserPreference::findOrFail($user_id);
        return response()->json($userPreference);
    }

    public function updateCurrentUserPreferences(Request $request): Response
    {
        $request->validate([
            'notice_method' => 'in:SMS,E-mail,Both',
            'city' => 'nullable|string|max:255',
            'meteorological_warnings' => 'boolean',
            'hydrological_warnings' => 'boolean',
            'air_quality_warnings' => 'boolean',
            'temperature_warning' => 'boolean',
            'temperature_check_value' => 'numeric|max:50|min:-50',
        ]);

        $user = $request->user();
        $preferences = $user->preference;

        if ($request->has('notice_method'))
        {
            $method = $request->input('notice_method');

            if ($method === 'E-mail' && !$user->email)
            {
                return response([
                    'message' => 'User does not have an e-mail provided, so it is not possible to notice over it.'
                ], 400);
            }

            if ($method === 'SMS' && !$user->phone_number)
            {
                return response([
                    'message' => 'User does not have a phone number provided, so it is not possible to notice over it.'
                ], 400);
            }

            $preferences->notice_method = $method;
        }

        if ($request->has('city'))
        {
            // TODO: Implement validation for the city
            $preferences->city = $request->input('city');
        }

        DB::transaction(function () use ($request, $preferences) {
            $preferences->meteorological_warnings = $request->input(
                'meteorological_warnings', $preferences->meteorological_warnings
            );
            $preferences->hydrological_warnings = $request->input(
                'hydrological_warnings', $preferences->hydrological_warnings
            );
            $preferences->air_quality_warnings = $request->input(
                'air_quality_warnings', $preferences->air_quality_warnings
            );
            $preferences->temperature_warning = $request->input(
                'temperature_warning', $preferences->temperature_warning
            );
            $preferences->temperature_check_value = $request->input(
                'temperature_check_value', $preferences->temperature_check_value
            );

            $preferences->save();
        });

        return response(
            [
                'message' => 'User preferences updated successfully.',
                'preferences' => $preferences
            ],
            200
        );
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id): JsonResource
    {
        $validatedData = $request->validate([
            'notice_method' => 'required|in:SMS,E-mail,Both',
            'city' => 'required|string|max:255',
            'meteorological_warnings' => 'boolean',
            'hydrological_warnings' => 'boolean',
            'air_quality_warnings' => 'boolean',
            'temperature_warning' => 'boolean',
            'temperature_check_value' => 'numeric|max:50|min:-50',
        ]);

        $userPreference = UserPreference::findOrFail($user_id);
        $userPreference->update($validatedData);

        return response()->json($userPreference);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id): JsonResource
    {
        $userPreference = UserPreference::findOrFail($user_id);
        $userPreference->delete();

        return response()->json(null, 204);
    }
}
