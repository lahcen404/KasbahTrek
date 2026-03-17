<?php
namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Interfaces\TourRepositoryInterface;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Validator;

class TourController extends Controller
{
    protected $tourRepository;

    public function __construct(TourRepositoryInterface $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    public function index()
    {
        $tours = $this->tourRepository->getAll();
        return response()->json($tours);
    }

    public function store(Request $request)
    {
        $validator = Validator::make($request->all(), [
            'title' => 'required|string|max:255',
            'description' => 'required|string',
            'location' => 'required|string',
            'price' => 'required|numeric',
            'difficulty' => 'required|string',
            'max_spots' => 'required|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $data = $request->all();
        $data['guide_id'] = auth()->id();

        $tour = $this->tourRepository->create($data);

        return response()->json([
            'message' => 'Tour created successfully !!!',
            'tour' => $tour
        ], 201);
    }

    public function show($id)
    {
        $tour = $this->tourRepository->findById($id);
        return response()->json($tour);
    }

    // updaaate a tour
    public function update(Request $request, $id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $validator = Validator::make($request->all(), [
            'title' => 'sometimes|string|max:255',
            'description' => 'sometimes|string',
            'location' => 'sometimes|string',
            'price' => 'sometimes|numeric',
            'difficulty' => 'sometimes|string',
            'max_spots' => 'sometimes|integer|min:1',
        ]);

        if ($validator->fails()) {
            return response()->json($validator->errors(), 422);
        }

        $updatedTour = $this->tourRepository->update($id, $request->all());

        return response()->json([
            'message' => 'Tour updated successfully!',
            'tour' => $updatedTour
        ]);
    }

    // deelete a tour
    public function destroy($id)
    {
        $tour = $this->tourRepository->findById($id);

        if ($tour->guide_id !== auth()->id()) {
            return response()->json(['message' => 'Unauthorized: You do not own this tour!!'], 403);
        }

        $this->tourRepository->delete($id);

        return response()->json([
            'message' => 'Tour deleted successfully!'
        ], 200);
    }

    // upload images
    public function uploadImages(Request $request, $id)
{
    $request->validate([
        'images.*' => 'required|image|mimes:jpeg,png,jpg'
    ]);

    $tour = $this->tourRepository->findById($id);
    $uploadedImages = [];

    if ($request->hasFile('images')) {
        foreach ($request->file('images') as $file) {
            // stooore the file in 'storage/app/public/tours'
            $path = $file->store('tours', 'public');

            // save in DB
            $imageRecord = $this->tourRepository->addImage($tour->id, $path);

            $uploadedImages[] = $imageRecord;
        }
    }

    return response()->json([
        'message' => 'Images uploaded successfully',
        'data' => $uploadedImages
    ], 201);
}
}
