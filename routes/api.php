<?php

use App\Http\Controllers\Api\AuthController;
use Illuminate\Support\Facades\Route;
use App\Http\Controllers\Api\RoleController;
use App\Http\Controllers\Api\PermissionController;
use App\Http\Controllers\Api\UserController;
use App\Http\Controllers\Api\CategoryController;
use App\Http\Controllers\Api\BookingController;
use App\Http\Controllers\Api\PaymentController;




Route::get('/artisans', [UserController::class,'artisans']);
Route::get('/artisans/{id}', [UserController::class,'show']);

Route::apiResource('categories', CategoryController::class);

Route::post('users/{user}/assign-categories',
    [UserController::class,'assignCategories']
);

Route::apiResource('roles', RoleController::class);
Route::apiResource('permissions', PermissionController::class);

Route::post('roles/{role}/assign-permission', [RoleController::class,'assignPermission']);
Route::post('users/{user}/assign-role', [UserController::class,'assignRole']);

Route::post('/register', [AuthController::class, 'register']);
Route::post('/login', [AuthController::class, 'login']);

Route::middleware('auth:sanctum')->group(function () {
    Route::get('/profile', [AuthController::class, 'profile']);
    Route::post('/logout', [AuthController::class, 'logout']);
    Route::put('/update-profile', [AuthController::class, 'updateProfile']);

    Route::post('/bookings', [BookingController::class,'store']);
    Route::get('/my-bookings', [BookingController::class,'myBookings']);
    Route::get('/artisan-bookings', [BookingController::class,'artisanBookings']);
    Route::put('/bookings/{booking}/accept', [BookingController::class,'accept']);
    Route::put('/bookings/{booking}/reject', [BookingController::class,'reject']);

    Route::post('/bookings/{booking}/pay',[PaymentController::class,'pay']);
    Route::post('/bookings/{booking}/release',[PaymentController::class,'release']);
});