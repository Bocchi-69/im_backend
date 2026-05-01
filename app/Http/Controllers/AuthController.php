<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController
{
    public function login(Request $request)
    {
        try {
            $credentials = $request->validate([
                'email'    => ['required', 'string', 'email'],
                'password' => ['required', 'string', 'min:6'],
            ]);

            if (! Auth::attempt($credentials)) {
                return response()->json([
                    'message' => 'Invalid Credentials',
                ], 401);
            }

            $user  = Auth::user();
            $token = $user->createToken('auth_token')->plainTextToken;

            return response()->json([
                'message' => 'Logged In Successfully',
                'token'   => $token,
                'user'    => [
                    'id'   => $user->id,
                    'name' => $user->name,
                    'email'=> $user->email,
                    'role' => $user->role,
                ],
            ]);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    public function register(Request $request)
    {
        try {
            $validated = $request->validate([
                'name'     => ['required', 'string', 'max:50'],
                'email'    => ['required', 'email', 'unique:users,email'],
                'password' => ['required', 'string', 'min:6'],
                'role'     => ['required', 'in:candidate,employer'],
            ]);

            $validated['password'] = Hash::make($validated['password']);

            User::create($validated);

            return response()->json([
                'message' => 'User Created Successfully',
            ], 201);
        } catch (ValidationException $e) {
            return response()->json([
                'message' => 'Something went wrong!',
                'error'   => $e->getMessage(),
            ], 422);
        }
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json([
            'message' => 'Logged Out Successfully',
        ]);
    }

    public function me(Request $request)
    {
        return response()->json([
            'user' => $request->user(),
        ]);
    }
}