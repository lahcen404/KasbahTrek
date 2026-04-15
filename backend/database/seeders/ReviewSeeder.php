<?php

namespace Database\Seeders;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Database\Seeder;

class ReviewSeeder extends Seeder
{
    public function run(): void
    {
        // Ensure there are confirmed bookings so reviews can be created for testing.
        $confirmedCount = Booking::query()
            ->where('status', BookingStatus::CONFIRMED->value)
            ->count();

        if ($confirmedCount < 5) {
            $needed = 5 - $confirmedCount;

            $pendingBookings = Booking::query()
                ->where('status', BookingStatus::PENDING->value)
                ->limit($needed)
                ->get();

            foreach ($pendingBookings as $booking) {
                $booking->update([
                    'status' => BookingStatus::CONFIRMED->value,
                    'payment_status' => PaymentStatus::PAID->value,
                ]);
            }
        }

        $confirmedBookings = Booking::query()
            ->where('status', BookingStatus::CONFIRMED->value)
            ->orderBy('id')
            ->get()
            ->unique(fn (Booking $booking) => $booking->traveler_id . '-' . $booking->tour_id)
            ->take(8);

        $comments = [
            'Amazing tour. The guide was friendly and very knowledgeable.',
            'Great organization and beautiful route, highly recommended.',
            'We learned a lot and had a very smooth experience.',
            'Excellent communication and perfect timing throughout the day.',
            'One of the best local experiences we had in Morocco.',
            'Very professional guide and memorable atmosphere.',
            'Worth every dirham. We would book again with this guide.',
            'Fantastic landscapes and a truly authentic experience.',
        ];

        foreach ($confirmedBookings as $index => $booking) {
            Review::query()->firstOrCreate(
                [
                    'traveler_id' => $booking->traveler_id,
                    'tour_id' => $booking->tour_id,
                ],
                [
                    'rating' => fake()->numberBetween(4, 5),
                    'comment' => $comments[$index % count($comments)],
                ]
            );
        }
    }
}
