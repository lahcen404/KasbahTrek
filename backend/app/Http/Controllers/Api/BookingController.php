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
        $request->validate(['status' => 'required|string']);

        $booking = $this->bookingRepository->updateStatus($id, $request->status);

        return response()->json([
            'message' => "Booking status updated to {$request->status}",
            'booking' => $booking
        ]);
    }
}
