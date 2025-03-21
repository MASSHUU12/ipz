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

    public function updateCurrentUser(Request $request)
    {
        $request->validate([
            'email' => 'sometimes|email|unique:users,email,',
            'phone_number' => 'sometimes|unique:users,phone_number|phone:INTERNATIONAL',
            'password' => [
                'sometimes',
                'string',
                'max:255',
                'min:8',
                'regex:/[a-z]/',
                'regex:/[A-Z]/',
                'regex:/[0-9]/',
                'regex:/[@$!%*?&]/',
            ],
        ], [
            'password.regex' => 'The password must contain at least one lowercase letter, one uppercase letter, one number, and one special character.',
        ]);

        $user = $request->user();

        if ($request->has('email')) {
            $user->email = $request->input('email');
        }

        if ($request->has('phone_number')) {
            $user->phone_number = $request->input('phone_number');
        }

        if ($request->has('password')) {
            $user->password = password_hash($request->input('password'), PASSWORD_DEFAULT);
        }

        $user->save();

        return response(
            ['message' => 'User updated successfully', 'user' => $user],
            200
        );
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
