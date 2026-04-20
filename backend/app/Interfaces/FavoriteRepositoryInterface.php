<?php

namespace App\Interfaces;

use App\Models\Favorite;
use Illuminate\Support\Collection;

interface FavoriteRepositoryInterface
{
    public function getAll(int $travelerId): Collection;

    public function add(int $travelerId, int $tourId): Favorite;

    public function remove(int $travelerId, int $tourId): bool;
}
