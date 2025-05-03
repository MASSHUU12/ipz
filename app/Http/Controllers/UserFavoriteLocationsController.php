<?php

namespace App\Http\Controllers;

use App\Models\UserFavoriteLocation;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class UserFavoriteLocationsController extends Controller
{
    /**
     * GET /api/favorites
     * Display a listing of the current user’s favorites.
     */
    public function index()
    {
        $favorites = Auth::user()
            ->favoriteLocations()
            ->select(['id', 'city', 'lat', 'lng'])
            ->get();

        return response()->json($favorites);
    }

    /**
     * POST /api/favorites
     * Store a newly created favorite location.
     */
    public function store(Request $request)
    {
        $data = $request->validate([
            'city'      => 'required|string|max:255',
            'lat'  => 'required|numeric|between:-90,90',
            'lng' => 'required|numeric|between:-180,180',
        ]);

        $favorite = $request->user()->favoriteLocations()->create($data);

        return response()->json($favorite, Response::HTTP_CREATED);
    }

    /**
     * GET /api/favorites/{favorite}
     * Display the specified favorite location.
     */
    public function show(UserFavoriteLocation $favorite)
    {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json(
                ['message' => 'This resource does not exist.'],
                Response::HTTP_NOT_FOUND
            );
        }

        return response()->json($favorite);
    }

    /**
     * PATCH /api/favorites/{favorite}
     * Update the specified favorite location.
     */
    public function update(Request $request, UserFavoriteLocation $favorite)
    {
        // TODO: Check if favorite exist
        if ($favorite->user_id !== $request->user()->id) {
            return response()->json(
                ['message' => 'This resource does not exist.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $data = $request->validate([
            'city'      => 'sometimes|required|string|max:255',
            'lat'  => 'sometimes|required|numeric|between:-90,90',
            'lng' => 'sometimes|required|numeric|between:-180,180',
        ]);

        $favorite->update($data);

        return response()->json($favorite);
    }

    /**
     * DELETE /api/favorites/{favorite}
     * Remove the specified favorite location.
     */
    public function destroy(UserFavoriteLocation $favorite)
    {
        if ($favorite->user_id !== Auth::id()) {
            return response()->json(
                ['message' => 'This resource does not exist.'],
                Response::HTTP_NOT_FOUND
            );
        }

        $favorite->delete();

        return response()->json(
            ['message' => 'Favorite location deleted.'],
            Response::HTTP_OK
        );
    }
}
