<?php

namespace App\Http\Controllers\Api;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Events\BookingStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Booking\StoreBookingRequest;
use App\Http\Requests\Api\Booking\UpdateBookingStatusRequest;
use App\Interfaces\BookingRepositoryInterface;
use App\Services\BookingPaymentService;
use App\Services\PayPalService;
use Illuminate\Http\Request;
use RuntimeException;
use Stripe\Exception\ApiErrorException;
use Stripe\StripeClient;

class BookingController extends Controller
{
    protected $bookingRepository;

    public function __construct(
        BookingRepositoryInterface $bookingRepository,
        private PayPalService $payPal,
        private BookingPaymentService $bookingPayment,
    ) {
        $this->bookingRepository = $bookingRepository;
    }

    public function store(StoreBookingRequest $request)
    {
        try {
            $booking = $this->bookingRepository->create($request->validated());

            return response()->json([
                'message' => 'Booking request sent successfully!',
                'booking' => $booking,
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    public function myBookings()
    {
        $bookings = $this->bookingRepository->getByTraveler(auth()->id());

        return response()->json($bookings);
    }

    public function guideBookings()
    {
        $bookings = $this->bookingRepository->getByGuide(auth()->id());

        return response()->json($bookings);
    }

    public function updateStatus(UpdateBookingStatusRequest $request, $id)
    {
        $booking = $this->bookingRepository->findById($id);

        if ($booking->guide_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized: This booking is for a tour you do not manage !!!',
            ], 403);
        }

        $status = $request->validated('status');

        if ($status === BookingStatus::CONFIRMED->value && $booking->payment_status !== PaymentStatus::PAID) {
            return response()->json([
                'message' => 'The traveler must complete payment before you can confirm this booking.',
            ], 422);
        }

        $updatedBooking = $this->bookingRepository->updateStatus($id, $status);

        // diispatch event to trigger the queue listener in the background
        event(new BookingStatusUpdated($updatedBooking));

        return response()->json([
            'message' => "Booking status updated to {$status}",
            'booking' => $updatedBooking,
        ]);
    }

    public function cancel($id)
    {
        $booking = $this->bookingRepository->findById($id);

        if ($booking->traveler_id !== auth()->id()) {
            return response()->json([
                'message' => 'Unauthorized: You cannot cancel someone else\'s booking.',
            ], 403);
        }

        if ($booking->status->value === 'CANCELLED') {
            return response()->json(['message' => 'This booking is already cancelled.'], 400);
        }

        $cancelledBooking = $this->bookingRepository->cancel($id);

        return response()->json([
            'message' => 'Booking cancelled successfully !!',
            'booking' => $cancelledBooking,
        ]);
    }

    public function checkout(int $id)
    {
        $booking = $this->bookingRepository->findById($id);

        if ($booking->traveler_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== BookingStatus::PENDING) {
            return response()->json(['message' => 'Only pending bookings can be paid for.'], 422);
        }

        if (! in_array($booking->payment_status, [PaymentStatus::UNPAID, PaymentStatus::FAILED], true)) {
            return response()->json(['message' => 'This booking is already paid or cannot be paid.'], 422);
        }

        $booking->loadMissing('tour');

        $secret = config('stripe.secret');
        if (! is_string($secret) || $secret === '') {
            return response()->json(['message' => 'Stripe is not configured'], 500);
        }

        try {
            $stripe = new StripeClient($secret);
            $amountMinor = (int) round(((float) $booking->total_price) * 100);

            $session = $stripe->checkout->sessions->create([
                'mode' => 'payment',
                'line_items' => [[
                    'price_data' => [
                        'currency' => config('stripe.currency'),
                        'unit_amount' => $amountMinor,
                        'product_data' => [
                            'name' => $booking->tour->title,
                            'description' => 'Booking #'.$booking->id,
                        ],
                    ],
                    'quantity' => 1,
                ]],
                'metadata' => [
                    'booking_id' => (string) $booking->id,
                ],
                'success_url' => config('stripe.success_url'),
                'cancel_url' => config('stripe.cancel_url'),
            ]);
        } catch (ApiErrorException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'url' => $session->url,
            'session_id' => $session->id,
        ]);
    }

    public function paypalCheckout(int $id)
    {
        $booking = $this->bookingRepository->findById($id);

        if ($booking->traveler_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->status !== BookingStatus::PENDING) {
            return response()->json(['message' => 'Only pending bookings can be paid for.'], 422);
        }

        if (! in_array($booking->payment_status, [PaymentStatus::UNPAID, PaymentStatus::FAILED], true)) {
            return response()->json(['message' => 'This booking is already paid or cannot be paid.'], 422);
        }

        $booking->loadMissing('tour');

        try {
            $created = $this->payPal->createOrder($booking);
            $booking->update(['paypal_order_id' => $created['order_id']]);
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        return response()->json([
            'approval_url' => $created['approval_url'],
            'order_id' => $created['order_id'],
        ]);
    }

    public function paypalCapture(int $id, Request $request)
    {
        $request->validate([
            'order_id' => ['required', 'string'],
        ]);

        $booking = $this->bookingRepository->findById($id);

        if ($booking->traveler_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized'], 403);
        }

        if ($booking->paypal_order_id !== $request->input('order_id')) {
            return response()->json(['message' => 'Order does not match this booking.'], 422);
        }

        try {
            $data = $this->payPal->captureOrder($request->input('order_id'));
        } catch (RuntimeException $e) {
            return response()->json(['message' => $e->getMessage()], 502);
        }

        if (($data['status'] ?? '') !== 'COMPLETED') {
            return response()->json(['message' => 'PayPal payment was not completed.'], 422);
        }

        $this->bookingPayment->markPaidAndSendReceipt($booking->fresh());

        return response()->json([
            'message' => 'Payment successful',
            'booking' => $booking->fresh(),
        ]);
    }
}
