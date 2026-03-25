<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Review\StoreReviewRequest;
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
}
