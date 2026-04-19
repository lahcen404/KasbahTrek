<?php

namespace App\Repositories;

use App\Interfaces\BookingRepositoryInterface;
use App\Models\Booking;
use App\Models\Tour;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class BookingRepository implements BookingRepositoryInterface
{
    public function create(array $data)
    {
        return DB::transaction(function () use ($data) {
            $travelerId = Auth::id();
            if (! $travelerId) {
                throw new \Exception('Unauthorized booking attempt.');
            }

            // Lock tour row to avoid race conditions when many travelers book at once.
            $tour = Tour::whereKey($data['tour_id'])->lockForUpdate()->firstOrFail();

            if (! $tour->date) {
                throw new \Exception('This tour does not have a valid date yet.');
            }

            $tourDate = Carbon::parse($tour->date)->toDateString();
            $bookingDate = Carbon::parse($data['date'])->toDateString();

            if ($bookingDate !== $tourDate) {
                throw new \Exception('Booking date must match the tour date selected by the guide.');
            }

            if (Carbon::parse($tourDate)->lt(Carbon::today())) {
                throw new \Exception('This tour date has already passed.');
            }

            // Prevent the same traveler from holding multiple active bookings for the same tour/date.
            $alreadyBooked = Booking::where('traveler_id', $travelerId)
                ->where('tour_id', $tour->id)
                ->whereDate('date', $tourDate)
                ->whereIn('status', ['PENDING', 'CONFIRMED'])
                ->exists();

            if ($alreadyBooked) {
                throw new \Exception('You already have an active booking for this tour date.');
            }

            if ($tour->current_bookings >= $tour->max_spots) {
                throw new \Exception('This tour is already full.');
            }

            $booking = Booking::create([
                'date' => $tourDate,
                'total_price' => $tour->price,
                'status' => 'PENDING',
                'traveler_id' => $travelerId,
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
        return DB::transaction(function () use ($id, $status) {
            $booking = Booking::whereKey($id)->lockForUpdate()->firstOrFail();
            $tour = Tour::whereKey($booking->tour_id)->lockForUpdate()->firstOrFail();

            $previous = strtoupper((string) ($booking->status->value ?? $booking->status));
            $next = strtoupper($status);

            if ($previous === $next) {
                return $booking;
            }

            $occupiesSpot = ['PENDING', 'CONFIRMED'];
            $wasOccupying = in_array($previous, $occupiesSpot, true);
            $willOccupy = in_array($next, $occupiesSpot, true);

            // If status starts occupying a spot, enforce capacity first.
            if (! $wasOccupying && $willOccupy) {
                if ($tour->current_bookings >= $tour->max_spots) {
                    throw new \Exception('Cannot update booking status because this tour is already full.');
                }
                $tour->increment('current_bookings');
            }

            // If status frees a spot, decrement safely.
            if ($wasOccupying && ! $willOccupy && $tour->current_bookings > 0) {
                $tour->decrement('current_bookings');
            }

            $booking->update(['status' => $next]);

            return $booking;
        });
    }

    // cancel booking
    public function cancel(int $id)
    {
        return DB::transaction(function () use ($id) {
            $booking = Booking::whereKey($id)->lockForUpdate()->firstOrFail();
            $tour = Tour::whereKey($booking->tour_id)->lockForUpdate()->firstOrFail();

            $current = strtoupper((string) ($booking->status->value ?? $booking->status));
            if ($current === 'CANCELLED') {
                return $booking;
            }

            if (in_array($current, ['PENDING', 'CONFIRMED'], true) && $tour->current_bookings > 0) {
                $tour->decrement('current_bookings');
            }

            $booking->update(['status' => 'CANCELLED']);

            return $booking;
        });
    }
}
