<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\Tour\StoreTourRequest;
use App\Http\Requests\Api\Tour\UpdateTourRequest;
use App\Http\Requests\Api\Tour\UploadTourImagesRequest;
use App\Interfaces\TourRepositoryInterface;
use Illuminate\Http\Request;

class TourController extends Controller
{
    protected $tourRepository;

    public function __construct(TourRepositoryInterface $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    public function index(Request $request)
    {
        return response()->json(
            $this->tourRepository->getAll(
                $request->except(['page', 'per_page']),
                (int) $request->query('per_page', 15)
            )
        );
    }

    public function store(StoreTourRequest $request)
    {
        $data = $request->validated();
        $data['guide_id'] = $request->user()->id;

        $tour = $this->tourRepository->create($data);

        $images = $request->file('images', []);

        foreach ($images as $file) {
            $path = $file->store('tours', 'public');
            $this->tourRepository->addImage($tour->id, $path);
        }

        $tour->load(['images:id,tour_id,path']);

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

    public function guideTours(Request $request)
    {
        $tours = $this->tourRepository->getByGuide($request->user()->id);

        return response()->json($tours);
    }

    public function update(UpdateTourRequest $request, $id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $validated = $request->validated();
        $removeImageIds = $validated['remove_image_ids'] ?? [];
        unset($validated['remove_image_ids']);

        $updatedTour = $this->tourRepository->update($id, $validated);

        foreach ($removeImageIds as $imageId) {
            $this->tourRepository->deleteImage((int) $tour->id, (int) $imageId);
        }

        return response()->json([
            'message' => 'Tour updated successfully!',
            'tour' => $updatedTour,
        ]);
    }

    public function destroy(Request $request, $id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== $request->user()->id) {
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

        if ($tour->guide_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

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

    public function deleteImage(Request $request, $tourId, $imageId)
    {
        $tour = $this->tourRepository->findById($tourId);

        if ($tour->guide_id !== $request->user()->id) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $this->tourRepository->deleteImage((int) $tourId, (int) $imageId);

        return response()->json([
            'message' => 'Image deleted successfully',
        ]);
    }
}
