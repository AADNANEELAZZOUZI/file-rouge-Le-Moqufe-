<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Review;
use App\Models\Booking;

class ReviewController extends Controller
{
    public function store(Request $request, Booking $booking)
    {
        if ($booking->client_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status != 'completed') {
            return response()->json(['message' => 'Service not completed'], 400);
        }

        if ($booking->review) {
            return response()->json(['message' => 'Already reviewed'], 400);
        }

        $request->validate([
            'rating' => 'required|integer|min:1|max:5',
            'comment' => 'nullable|string'
        ]);

        $review = Review::create([
            'booking_id' => $booking->id,
            'client_id' => auth()->id(),
            'artisan_id' => $booking->artisan_id,
            'rating' => $request->rating,
            'comment' => $request->comment
        ]);

        return response()->json($review, 201);
    }
    public function artisanReviews($artisanId)
    {
    return response()->json(
        Review::where('artisan_id', $artisanId)
            ->with('client')
            ->latest()
            ->get()
    );

    }

    public function artisanRating($artisanId)
    {
        $avg = Review::where('artisan_id', $artisanId)->avg('rating');

        return response()->json([
            'average_rating' => round($avg, 1)
        ]);
    }

}
