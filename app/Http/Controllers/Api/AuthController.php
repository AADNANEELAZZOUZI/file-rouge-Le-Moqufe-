<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;
use App\Models\User;
use App\Models\Role;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required|string',
        ]);

        if (!auth()->attempt($request->only('email', 'password'))) {
            return response()->json(['message' => 'Invalid credentials'], 401);
        }

        $user = auth()->user();
        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'access_token' => $token,
            'token_type' => 'Bearer',
            'user' => $user->load('roles'),
        ], 200);
    }

    public function logout(Request $request)
    {
        $request->user()->currentAccessToken()->delete();
        return response()->json(['message' => 'Logged out successfully'], 200);
    }

    public function register(Request $request)
    {
        $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|email|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
            'city' => 'nullable|string|max:255',
            'role' => 'required|in:client,artisan',

            'phone' => 'required_if:role,artisan|nullable|string|max:20',
            'bio' => 'nullable|string|max:500',

            'categories' => 'array',
            'categories.*' => 'exists:categories,id',
        ]);

        $user = User::create([
            'name' => $request->name,
            'email' => $request->email,
            'password' => Hash::make($request->password),
            'city' => $request->city,
            'phone' => $request->phone,
            'bio' => $request->bio,
        ]);

        $role = Role::where('name', $request->role)->first();
        if ($role) {
            $user->roles()->attach($role->id);
        }

        if ($request->role === 'artisan' && $request->categories) {
            $user->categories()->attach($request->categories);
        }

        $token = $user->createToken('auth_token')->plainTextToken;

        return response()->json([
            'user' => $user->load(['roles', 'categories']),
            'access_token' => $token,
            'token_type' => 'Bearer',
        ], 201);
    }

    public function profile(Request $request)
    {
        return response()->json(
            $request->user()->load(['roles', 'categories'])
        );
    }

    public function updateProfile(Request $request)
    {
        $request->validate([
            'name' => 'sometimes|string|max:255',
            'email' => 'sometimes|email|unique:users,email,' . auth()->id(),
            'city' => 'sometimes|nullable|string|max:255',
            'phone' => 'sometimes|nullable|string|max:20',
            'bio' => 'sometimes|nullable|string|max:500',
            'password' => 'sometimes|string|min:8|confirmed',
            'current_password' => 'required_with:password|string',
            'categories' => 'sometimes|array',
            'categories.*' => 'exists:categories,id',
        ]);

        $user = auth()->user();

        if ($request->filled('password')) {
            if (!Hash::check($request->current_password, $user->password)) {
                return response()->json(['message' => 'Current password is incorrect'], 422);
            }
            $user->password = Hash::make($request->password);
        }

        $user->update($request->only('name', 'email', 'city', 'phone', 'bio'));

        if ($request->has('categories')) {
            $user->categories()->sync($request->categories);
        }

        return response()->json([
            'message' => 'Profile updated successfully',
            'user' => $user->load(['roles', 'categories']),
        ], 200);
    }
}
