<?php
namespace App\Repositories;

use App\Interfaces\TourRepositoryInterface;
use App\Models\Image;
use App\Models\Tour;

class TourRepository implements TourRepositoryInterface
{
    public function getAll()
    {
        return Tour::with(['guide', 'images'])->get();
    }

    public function findById(int $id)
    {
        return Tour::with(['guide', 'images', 'reviews'])->findOrFail($id);
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
        return Tour::where('guide_id', $guideId)->with('images')->get();
    }

    public function addImage(int $tourId, string $path)
{
    return Image::create([
        'tour_id' => $tourId,
        'path' => $path
    ]);
}
}
