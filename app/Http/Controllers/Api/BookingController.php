<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Booking;

class BookingController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'artisan_id' => 'required|exists:users,id',
            'booking_date' => 'required|date',
            'description' => 'nullable|string'
        ]);

        $booking = Booking::create([
            'client_id' => auth()->id(),
            'artisan_id' => $request->artisan_id,
            'booking_date' => $request->booking_date,
            'description' => $request->description,
            'status' => 'pending'
        ]);

        return response()->json($booking, 201);
    }

    public function artisanBookings()
    {
        return Booking::where('artisan_id', auth()->id())
            ->with('client')
            ->latest()
            ->get();
    }
    public function myBookings()
    {
        return Booking::where('client_id', auth()->id())
            ->with('artisan')
            ->latest()
            ->get();
    }

    public function accept(Booking $booking)
    {
        if ($booking->artisan_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status != 'pending') {
            return response()->json(['message' => 'Already processed'], 400);
        }

        $booking->update([
            'status' => 'accepted'
        ]);

        return response()->json(['message' => 'Booking accepted']);
    }
    public function reject(Booking $booking)
    {
        if ($booking->artisan_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        $booking->update([
            'status' => 'rejected'
        ]);

        return response()->json(['message' => 'Booking rejected']);
    }
}
 