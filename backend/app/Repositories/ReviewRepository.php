<?php

namespace App\Repositories;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use App\Interfaces\ReviewRepositoryInterface;
use App\Models\Booking;
use App\Models\Review;
use Illuminate\Support\Collection;

class ReviewRepository implements ReviewRepositoryInterface
{
    public function getMyReviews(int $travelerId): Collection
    {
        return Review::query()
            ->where('traveler_id', $travelerId)
            ->with(['tour', 'traveler'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getGuideReviews(int $guideId): Collection
    {
        return Review::query()
            ->whereHas('tour', function ($query) use ($guideId) {
                $query->where('guide_id', $guideId);
            })
            ->with([
                'traveler:id,fullname',
                'tour:id,title,guide_id',
            ])
            ->orderByDesc('created_at')
            ->get();
    }

    public function getTourReviews(int $tourId): Collection
    {
        return Review::query()
            ->where('tour_id', $tourId)
            ->with('traveler')
            ->orderByDesc('created_at')
            ->get();
    }

    public function add(int $travelerId, array $data): Review
    {
        $tourId = (int) $data['tour_id'];

        $hasEligibleBooking = Booking::query()
            ->where('traveler_id', $travelerId)
            ->where('tour_id', $tourId)
            ->where('status', BookingStatus::CONFIRMED->value)
            ->where('payment_status', PaymentStatus::PAID->value)
            ->exists();

        if (! $hasEligibleBooking) {
            throw new \InvalidArgumentException('You can review only tours that were confirmed and paid.');
        }

        $alreadyReviewed = Review::query()
            ->where('traveler_id', $travelerId)
            ->where('tour_id', $tourId)
            ->exists();

        if ($alreadyReviewed) {
            throw new \InvalidArgumentException('You already reviewed this tour.');
        }

        return Review::query()->create([
            'traveler_id' => $travelerId,
            'tour_id' => $tourId,
            'rating' => (int) $data['rating'],
            'comment' => $data['comment'],
        ]);
    }

    public function update(int $travelerId, int $reviewId, array $data): Review
    {
        $review = Review::query()
            ->where('id', $reviewId)
            ->where('traveler_id', $travelerId)
            ->first();

        if (! $review) {
            throw new \InvalidArgumentException('Review not found or not owned by you.');
        }

        $review->update([
            'rating' => (int) $data['rating'],
            'comment' => $data['comment'],
        ]);

        return $review->fresh(['tour', 'traveler']);
    }

    public function delete(int $travelerId, int $reviewId): void
    {
        $review = Review::query()
            ->where('id', $reviewId)
            ->where('traveler_id', $travelerId)
            ->first();

        if (! $review) {
            throw new \InvalidArgumentException('Review not found or not owned by you.');
        }

        $review->delete();
    }
}
