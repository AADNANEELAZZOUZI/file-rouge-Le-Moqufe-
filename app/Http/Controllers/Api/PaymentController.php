<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;

class PaymentController extends Controller
{
    public function pay(Booking $booking)
    {
        if ($booking->client_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status != 'accepted') {
            return response()->json(['message' => 'Booking not accepted yet'], 400);
        }

        $amount = $booking->price;
        $commission = $amount * 0.10; // 10%
        $artisanAmount = $amount - $commission;

        $payment = Payment::create([
            'booking_id' => $booking->id,
            'amount' => $amount,
            'commission' => $commission,
            'artisan_amount' => $artisanAmount,
            'status' => 'held'
        ]);

        $booking->update([
            'status' => 'paid'
        ]);

        return response()->json($payment);
    }
    public function release(Booking $booking)
    {
        // غير client يقدر يأكد الخدمة
        if ($booking->client_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status != 'paid') {
            return response()->json(['message' => 'Payment not done'], 400);
        }

        $payment = $booking->payment;

        $payment->update([
            'status' => 'released'
        ]);

        $booking->update([
            'status' => 'completed'
        ]);

        return response()->json(['message' => 'Money released to artisan']);
    }
}
