<?php

namespace App\Interfaces;

use App\Models\Review;
use Illuminate\Support\Collection;

interface ReviewRepositoryInterface
{
    public function getMyReviews(int $travelerId): Collection;

    public function getGuideReviews(int $guideId): Collection;

    public function getTourReviews(int $tourId): Collection;

    public function add(int $travelerId, array $data): Review;
}
