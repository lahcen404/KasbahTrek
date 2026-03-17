<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\BookingRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class BookingController extends Controller
{
    protected $bookingRepository;

    public function __construct(BookingRepositoryInterface $bookingRepository)
    {
        $this->bookingRepository = $bookingRepository;
    }

    // creaate booking
    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'tour_id' => 'required|exists:tours,id',
            'date' => 'required|date|after:today',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        try {
            $booking = $this->bookingRepository->create($request->all());
            return response()->json([
                'message' => 'Booking request sent successfully!',
                'booking' => $booking
            ], 201);
        } catch (\Exception $e) {
            return response()->json(['message' => $e->getMessage()], 400);
        }
    }

    // as traveler get my bookings
    public function myBookings()
    {
        $bookings = $this->bookingRepository->getByTraveler(auth()->id());
        return response()->json($bookings);
    }

    // as guide get bookings for my tours
    public function guideBookings()
    {
        $bookings = $this->bookingRepository->getByGuide(auth()->id());
        return response()->json($bookings);
    }

    // as guide update booking status
   public function updateStatus(Request $request, $id)
{
    // find the booking
    $booking = $this->bookingRepository->findById($id);

    // check is booking belongs to guide
    if ($booking->guide_id !== auth()->id()) {
        return response()->json([
            'message' => 'Unauthorized: This booking is for a tour you do not manage !!!'
        ], 403);
    }

    // vaalidation
    $request->validate([
        'status' => 'required|string|in:CONFIRMED,CANCELLED'
    ]);


    $updatedBooking = $this->bookingRepository->updateStatus($id, $request->status);

    return response()->json([
        'message' => "Booking status updated to {$request->status}",
        'booking' => $updatedBooking
    ]);
}

// cancel booking as traveler
public function cancel(Request $request, $id)
{

    $booking = $this->bookingRepository->findById($id);

    // check if booking belongs to the authenticated traveler
    if ($booking->traveler_id !== auth()->id()) {
        return response()->json([
            'message' => 'Unauthorized: You cannot cancel someone else\'s booking.'
        ], 403);
    }

    if ($booking->status->value === 'CANCELLED') {
        return response()->json(['message' => 'This booking is already cancelled.'], 400);
    }

    // 4. Proceed with cancellation
    $cancelledBooking = $this->bookingRepository->cancel($id);

    return response()->json([
        'message' => 'Booking cancelled successfully !!',
        'booking' => $cancelledBooking
    ]);
}

}
