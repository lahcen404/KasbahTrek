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
}
