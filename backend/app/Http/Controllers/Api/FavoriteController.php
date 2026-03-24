<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Favorite\StoreFavoriteRequest;
use App\Interfaces\FavoriteRepositoryInterface;

class FavoriteController extends Controller
{
    public function __construct(
        protected FavoriteRepositoryInterface $favorites
    ) {}

    public function list()
    {
        $items = $this->favorites->getAll(auth()->id());

        return response()->json($items);
    }

    public function add(StoreFavoriteRequest $request)
    {
        try {
            $favorite = $this->favorites->add(auth()->id(), (int) $request->validated('tour_id'));

            return response()->json([
                'message' => 'Tour added to favorites.',
                'favorite' => $favorite->load(['tour.guide', 'tour.images']),
            ], 201);
        } catch (\InvalidArgumentException $e) {
            return response()->json(['message' => $e->getMessage()], 409);
        }
    }

    public function remove(int $tourId)
    {
        $ok = $this->favorites->remove(auth()->id(), $tourId);

        if (! $ok) {
            return response()->json([
                'message' => 'Favorite not found for this tour.',
            ], 404);
        }

        return response()->json([
            'message' => 'Tour removed from favorites.',
        ]);
    }
}
