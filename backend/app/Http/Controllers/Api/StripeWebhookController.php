<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Events\BookingPaymentReceived;
use App\Http\Controllers\Controller;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Stripe\Exception\SignatureVerificationException;
use Stripe\Webhook;
use UnexpectedValueException;

class StripeWebhookController extends Controller
{
    public function handle(Request $request): Response|\Illuminate\Http\JsonResponse
    {
        $payload = $request->getContent();
        $sigHeader = $request->header('Stripe-Signature');
        $secret = config('stripe.webhook_secret');

        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Webhook not configured'], 500);
        }

        try {
            $event = Webhook::constructEvent($payload, (string) $sigHeader, $secret);
        } catch (UnexpectedValueException|SignatureVerificationException $e) {
            return response()->json(['message' => 'Invalid payload'], 400);
        }

        if ($event->type === 'checkout.session.completed') {
            /** @var \Stripe\Checkout\Session $session */
            $session = $event->data->object;
            $bookingId = $session->metadata['booking_id'] ?? null;

            if ($bookingId === null || $bookingId === '') {
                return response()->json(['message' => 'Missing booking_id'], 400);
            }

            $booking = Booking::query()->find($bookingId);
            if (! $booking) {
                return response()->json(['message' => 'Booking not found'], 404);
            }

            if ($booking->payment_status === PaymentStatus::PAID) {
                return response()->json(['received' => true]);
            }

            if (($session->payment_status ?? '') !== 'paid') {
                return response()->json(['received' => true]);
            }

            $booking->update([
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => now(),
            ]);

            $booking->refresh()->load(['traveler', 'tour']);
            event(new BookingPaymentReceived($booking));
        }

        return response()->json(['received' => true]);
    }
}
