<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public function artisans(Request $request)
    {
        $query = User::whereHas('roles', fn($q) => $q->where('name', 'artisan'));

        if ($request->category) {
            $query->whereHas('categories', fn($q) => $q->where('categories.id', $request->category));
        }

        if ($request->city) {
            $query->where('city', $request->city);
        }

        return $query->with('categories')->get();
    }


    public function show($id)
    {
        $artisan = User::with([
            'categories',
            'artisanBookings.review'
        ])->findOrFail($id);

        return $artisan;
    }
    public function assignRole(Request $request, User $user)
    {
        $request->validate([
            'role_id' => 'required|exists:roles,id'
        ]);

        $user->roles()->sync([$request->role_id]);

        return response()->json(['message' => 'Role assigned successfully']);
    }

    public function assignCategories(Request $request, User $user)
    {
        $request->validate([
            'categories' => 'required|array',
            'categories.*' => 'exists:categories,id'
        ]);

        $user->categories()->sync($request->categories);

        return response()->json(['message' => 'Categories assigned']);
    }
}
