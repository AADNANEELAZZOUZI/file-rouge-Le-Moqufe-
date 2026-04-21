<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\User;

class UserController extends Controller
{

    public function artisans(Request $request)
    {
        $query = User::whereHas('roles', function ($q) {
            $q->where('name', 'artisan');
        })->with('categories');

        if ($request->search) {
            $query->where(function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->search . '%')
                    ->orWhere('email', 'like', '%' . $request->search . '%');
            });
        }

        if ($request->category_id) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('categories.id', $request->category_id);
            });
        }

        return response()->json($query->latest()->get());
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
    public function search(Request $request)
    {
        $query = User::with('categories')
            ->whereHas('roles', fn($q) => $q->where('name', 'artisan'));

        if ($request->name) {
            $query->where('name', 'like', '%' . $request->name . '%');
        }

        if ($request->city) {
            $query->where('city', 'like', '%' . $request->city . '%');
        }

        if ($request->service) {
            $query->whereHas('categories', function ($q) use ($request) {
                $q->where('name', 'like', '%' . $request->service . '%');
            });
        }

        return response()->json($query->get());
    }
}
