<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tour\StoreTourRequest;
use App\Http\Requests\Api\Tour\UpdateTourRequest;
use App\Http\Requests\Api\Tour\UploadTourImagesRequest;
use App\Interfaces\TourRepositoryInterface;

class TourController extends Controller
{
    protected $tourRepository;

    public function __construct(TourRepositoryInterface $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    public function index(\Illuminate\Http\Request $request)
    {
        $tours = $this->tourRepository->getAll($request->all());

        return response()->json($tours);
    }

    public function store(StoreTourRequest $request)
    {
        $data = $request->validated();
        $data['guide_id'] = auth()->id();

        $tour = $this->tourRepository->create($data);

        return response()->json([
            'message' => 'Tour created successfully !!!',
            'tour' => $tour,
        ], 201);
    }

    public function show($id)
    {
        $tour = $this->tourRepository->findById($id);

        return response()->json($tour);
    }

    public function update(UpdateTourRequest $request, $id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $updatedTour = $this->tourRepository->update($id, $request->validated());

        return response()->json([
            'message' => 'Tour updated successfully!',
            'tour' => $updatedTour,
        ]);
    }

    public function destroy($id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $this->tourRepository->delete($id);

        return response()->json([
            'message' => 'Tour deleted successfully!',
        ], 200);
    }

    public function uploadImages(UploadTourImagesRequest $request, $id)
    {
        $tour = $this->tourRepository->findById($id);
        $uploadedImages = [];

        foreach ($request->file('images') as $file) {
            $path = $file->store('tours', 'public');
            $imageRecord = $this->tourRepository->addImage($tour->id, $path);
            $uploadedImages[] = $imageRecord;
        }

        return response()->json([
            'message' => 'Images uploaded successfully',
            'data' => $uploadedImages,
        ], 201);
    }
}
