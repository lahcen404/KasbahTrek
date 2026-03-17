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

    // upload images
    public function uploadImages(Request $request, $id)
{
    $request->validate([
        'images.*' => 'required|image|mimes:jpeg,png,jpg|max:2048' // max 2MB per image
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
