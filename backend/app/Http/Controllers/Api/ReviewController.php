<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Review\StoreReviewRequest;
use App\Http\Requests\Api\Review\UpdateReviewRequest;
use App\Interfaces\ReviewRepositoryInterface;
use Illuminate\Support\Facades\Auth;

class ReviewController extends Controller
{
    public function __construct(
        protected ReviewRepositoryInterface $reviews
    ) {}

    public function myReviews()
    {
        $items = $this->reviews->getMyReviews((int) Auth::id());

        return response()->json($items);
    }

    public function guideReviews()
    {
        $items = $this->reviews->getGuideReviews((int) Auth::id());

        return response()->json($items);
    }

    public function tourReviews(int $id)
    {
        $items = $this->reviews->getTourReviews($id);

        return response()->json($items);
    }

    public function add(StoreReviewRequest $request)
    {
        try {
            $review = $this->reviews->add((int) Auth::id(), $request->validated());

            return response()->json([
                'message' => 'Review added successfully.',
                'review' => $review->load(['tour', 'traveler']),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 409);
        }
    }

    public function update(UpdateReviewRequest $request, int $id)
    {
        try {
            $review = $this->reviews->update((int) Auth::id(), $id, $request->validated());

            return response()->json([
                'message' => 'Review updated successfully.',
                'review' => $review,
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }

    public function delete(int $id)
    {
        try {
            $this->reviews->delete((int) Auth::id(), $id);

            return response()->json([
                'message' => 'Review deleted successfully.',
            ]);
        } catch (\InvalidArgumentException $e) {
            return response()->json([
                'message' => $e->getMessage(),
            ], 404);
        }
    }
}
