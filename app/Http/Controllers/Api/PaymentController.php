<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use App\Models\Payment;
use App\Models\Booking;
use Stripe\Stripe;
use Stripe\Checkout\Session;

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

        if ($booking->payment) {
            return response()->json(['message' => 'Payment already exists'], 400);
        }

        $amount      = $booking->price;
        $commission  = round($amount * 0.10, 2);
        $artisanAmount = $amount - $commission;

        Stripe::setApiKey(config('services.stripe.secret'));

        $session = Session::create([
            'payment_method_types' => ['card'],
            'line_items' => [[
                'price_data' => [
                    'currency'     => 'eur', // changer en 'mad' si Stripe supporte
                    'unit_amount'  => (int)($amount * 100), // Stripe katkhdm centimes
                    'product_data' => [
                        'name'        => 'Réservation #' . $booking->id,
                        'description' => $booking->description ?? 'Service artisan - Le Moqufe',
                    ],
                ],
                'quantity' => 1,
            ]],
            'mode'        => 'payment',
            'success_url' => env('FRONTEND_URL') . '/payment/success?booking_id=' . $booking->id,
            'cancel_url'  => env('FRONTEND_URL') . '/payment/cancel?booking_id=' . $booking->id,
            'metadata'    => [
                'booking_id'    => $booking->id,
                'client_id'     => $booking->client_id,
                'artisan_id'    => $booking->artisan_id,
            ],
        ]);

        Payment::create([
            'booking_id'        => $booking->id,
            'amount'            => $amount,
            'commission'        => $commission,
            'artisan_amount'    => $artisanAmount,
            'status'            => 'pending',  // pending → held après confirmation Stripe
            'stripe_session_id' => $session->id,
        ]);

        return response()->json([
            'checkout_url' => $session->url,
        ]);
    }

    public function webhook(Request $request)
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');

        try {
            $event = \Stripe\Webhook::constructEvent(
                $payload,
                $sigHeader,
                config('services.stripe.webhook_secret')
            );
        } catch (\Exception $e) {
            return response()->json(['message' => 'Invalid signature'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            $session   = $event->data->object;
            $bookingId = $session->metadata->booking_id;

            $booking = Booking::find($bookingId);
            if ($booking) {
                $booking->update(['status' => 'paid']);

                $payment = Payment::where('stripe_session_id', $session->id)->first();
                if ($payment) {
                    $payment->update(['status' => 'held']);
                }
            }
        }

        return response()->json(['message' => 'Webhook received']);
    }

    public function release(Booking $booking)
    {
        if ($booking->client_id != auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status != 'paid') {
            return response()->json(['message' => 'Payment not done'], 400);
        }

        $payment = $booking->payment;

        if (!$payment) {
            return response()->json(['message' => 'Payment not found'], 404);
        }

        $payment->update(['status' => 'released']);
        $booking->update(['status' => 'completed']);

        return response()->json(['message' => 'Money released to artisan']);
    }
}