<?php

namespace App\Repositories;

use App\Interfaces\TourRepositoryInterface;
use App\Models\Image;
use App\Models\Tour;
use Illuminate\Contracts\Pagination\Paginator;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;

class TourRepository implements TourRepositoryInterface
{
    public function getAll(array $filters = [], ?int $perPage = null): Collection|Paginator
    {
        unset($filters['page'], $filters['per_page']);
        $query = $this->filteredQuery($filters);

        if ($perPage === null) {
            return $query->get();
        }

        $perPage = max(1, min(50, $perPage));

        return $query->latest('tours.id')
            ->simplePaginate($perPage)
            ->withQueryString();
    }

    private function filteredQuery(array $filters): Builder
    {
        $query = Tour::with(['guide', 'images', 'category']);

        if (isset($filters['search'])) {
            $query->where(function ($q) use ($filters) {
                $q->where('title', 'ilike', '%'.$filters['search'].'%')
                    ->orWhere('description', 'ilike', '%'.$filters['search'].'%');
            });
        }

        if (isset($filters['location'])) {
            $query->where('location', 'ilike', '%'.$filters['location'].'%');
        }

        if (! empty($filters['category_id'])) {
            $query->where('category_id', $filters['category_id']);
        }

        if (isset($filters['difficulty'])) {
            $query->where('difficulty', $filters['difficulty']);
        }

        if (isset($filters['min_price'])) {
            $query->where('price', '>=', $filters['min_price']);
        }

        if (isset($filters['max_price'])) {
            $query->where('price', '<=', $filters['max_price']);
        }

        if (isset($filters['min_duration_hours'])) {
            $query->where('duration_hours', '>=', $filters['min_duration_hours']);
        }

        if (isset($filters['max_duration_hours'])) {
            $query->where('duration_hours', '<=', $filters['max_duration_hours']);
        }

        if ($this->filterTruthy($filters['verified_only'] ?? null)) {
            $query->whereHas('guide', function ($q) {
                $q->where('is_verified', true);
            });
        }

        if ($this->filterTruthy($filters['available_only'] ?? null)) {
            $query->whereColumn('current_bookings', '<', 'max_spots');
        }

        return $query;
    }

    private function filterTruthy(mixed $value): bool
    {
        if ($value === null || $value === '') {
            return false;
        }

        if (is_bool($value)) {
            return $value;
        }

        if (is_string($value)) {
            return in_array(strtolower($value), ['1', 'true', 'yes'], true);
        }

        return (bool) $value;
    }

    public function findById(int $id)
    {
        return Tour::with([
            'guide:id,fullname,email,is_verified',
            'images:id,tour_id,path',
            'category:id,name,description',
            'reviews:id,tour_id,traveler_id,rating,comment,created_at',
            'reviews.traveler:id,fullname',
        ])
            ->withAvg('reviews as rating_avg', 'rating')
            ->withCount('reviews')
            ->findOrFail($id);
    }

    public function create(array $data)
    {
        return Tour::create($data);
    }

    public function update(int $id, array $data)
    {
        $tour = $this->findById($id);
        $tour->update($data);

        return $tour;
    }

    public function delete(int $id)
    {
        $tour = $this->findById($id);

        return $tour->delete();
    }

    public function getByGuide(int $guideId)
    {
        return Tour::where('guide_id', $guideId)
            ->with([
                'images:id,tour_id,path',
                'guide:id,fullname,is_verified',
            ])
            ->withCount('bookings')
            ->latest('id')
            ->get();
    }

    public function addImage(int $tourId, string $path)
    {
        return Image::create([
            'tour_id' => $tourId,
            'path' => $path,
        ]);
    }

    public function deleteImage(int $tourId, int $imageId): bool
    {
        $image = Image::where('tour_id', $tourId)->where('id', $imageId)->first();

        if (! $image) {
            return false;
        }

        if (! empty($image->path)) {
            Storage::disk('public')->delete($image->path);
        }

        return (bool) $image->delete();
    }
}
