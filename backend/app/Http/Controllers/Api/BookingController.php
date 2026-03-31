<?php

namespace App\Http\Controllers\Api;

use App\Events\BookingStatusUpdated;
use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Booking\StoreBookingRequest;
use App\Http\Requests\Api\Booking\UpdateBookingStatusRequest;
use App\Interfaces\BookingRepositoryInterface;

class BookingController extends Controller
{
    protected $bookingRepository;

    public function __construct(BookingRepositoryInterface $bookingRepository)
    {
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
}
