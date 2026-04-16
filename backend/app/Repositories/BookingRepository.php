<?php

namespace App\Repositories;

use App\Interfaces\BookingRepositoryInterface;
use App\Models\Booking;
use App\Models\Tour;
use Illuminate\Support\Facades\DB;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data)
    {
        // fiinf the tour
        $tour = Tour::findOrFail($data['tour_id']);

        // cheeck if tour full
        if ($tour->current_bookings >= $tour->max_spots) {
            throw new \Exception("This tour is already full!!!");
        }

        // creaate the booking
        return DB::transaction(function () use ($data, $tour) {
            $booking = Booking::create([
                'date' => $data['date'],
                'total_price' => $tour->price,
                'status' => 'PENDING',
                'traveler_id' => auth()->id(),
                'tour_id' => $tour->id,
                'guide_id' => $tour->guide_id,
            ]);


            $tour->increment('current_bookings');

            return $booking;
        });
    }


    public function findById(int $id){
        return Booking::with(['tour', 'traveler'])->findOrFail($id);
    }

    public function getByTraveler(int $travelerId)
    {
           return Booking::where('traveler_id', $travelerId)->with('tour.images')->get();
    }

    public function getByGuide(int $guideId)
    {
        return Booking::where('guide_id', $guideId)
            ->with([
                'traveler:id,fullname,email',
                'tour:id,title,description,location,price,guide_id',
                'tour.images:id,tour_id,path',
                'tour.guide:id,fullname,is_verified',
            ])
            ->latest('id')
            ->get();
    }

    public function updateStatus(int $id, string $status)
    {
        $booking = Booking::findOrFail($id);
        $booking->update(['status' => $status]);
        return $booking;
    }

    // cancel booking
    public function cancel(int $id)
{
    return DB::transaction(function () use ($id) {
        $booking = Booking::findOrFail($id);


        $booking->update(['status' => 'CANCELLED']);

        // decrement current_bookings for the tour
        $tour = Tour::findOrFail($booking->tour_id);
        if ($tour->current_bookings > 0) {
            $tour->decrement('current_bookings');
        }

        return $booking;
    });
}
}
