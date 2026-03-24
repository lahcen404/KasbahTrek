<?php

namespace App\Repositories;

use App\Interfaces\FavoriteRepositoryInterface;
use App\Models\Favorite;
use App\Models\Tour;
use Illuminate\Support\Collection;

class FavoriteRepository implements FavoriteRepositoryInterface
{
    public function getAll(int $travelerId): Collection
    {
        return Favorite::query()
            ->where('traveler_id', $travelerId)
            ->with(['tour.guide', 'tour.images'])
            ->orderByDesc('created_at')
            ->get();
    }

    public function add(int $travelerId, int $tourId): Favorite
    {
        Tour::query()->findOrFail($tourId);

        if (Favorite::query()->where('traveler_id', $travelerId)->where('tour_id', $tourId)->exists()) {
            throw new \InvalidArgumentException('This tour is already in your favorites.');
        }

        return Favorite::query()->create([
            'traveler_id' => $travelerId,
            'tour_id' => $tourId,
        ]);
    }

    public function remove(int $travelerId, int $tourId): bool
    {
        $deleted = Favorite::query()
            ->where('traveler_id', $travelerId)
            ->where('tour_id', $tourId)
            ->delete();

        return $deleted > 0;
    }
}
