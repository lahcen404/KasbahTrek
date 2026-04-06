<?php

namespace App\Http\Controllers\Api;

use App\Enums\PaymentStatus;
use App\Http\Controllers\Controller;
use App\Mail\BookingPaymentReceivedMail;
use App\Models\Booking;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;
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

            if (($session->payment_status ?? '') !== 'paid') {
                return response()->json(['received' => true]);
            }

            if ($booking->payment_status === PaymentStatus::PAID) {
                $this->sendPaymentReceiptIfNeeded($booking);

                return response()->json(['received' => true]);
            }

            $booking->update([
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => now(),
            ]);

            $booking->refresh()->load(['traveler', 'tour']);
            $this->sendPaymentReceiptIfNeeded($booking);
        }

        return response()->json(['received' => true]);
    }

    /**
     * Never throw: Stripe must get 2xx after we persist payment, or retries skip the email.
     * Retries duplicate webhooks when receipt was not sent yet (see payment_receipt_sent_at).
     */
    private function sendPaymentReceiptIfNeeded(Booking $booking): void
    {
        if ($booking->payment_receipt_sent_at !== null) {
            return;
        }

        if ($booking->payment_status !== PaymentStatus::PAID) {
            return;
        }

        try {
            $booking->loadMissing('traveler', 'tour');

            Mail::to($booking->traveler->email)
                ->send(new BookingPaymentReceivedMail($booking));

            $booking->forceFill(['payment_receipt_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Payment receipt email failed', [
                'booking_id' => $booking->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
