<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;

class UserController extends Controller
{
    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        //
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        //
    }

    public function showCurrentUser(Request $request)
    {
        return response([
            'user' => $request->user()
        ], 200);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        //
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        //
    }

    public function destroyCurrentUser(Request $request)
    {
        try {
            $request->user()->delete();
        } catch (\Exception)
        {
            return response(
                ['message' => 'There was an error during user deletion'],
                500
            );
        }

        return response(['message' => 'User deleted successfully'], 200);
    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        //
    }
}
