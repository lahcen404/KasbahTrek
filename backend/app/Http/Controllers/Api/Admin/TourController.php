<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\TourRepositoryInterface;
use Illuminate\Http\Request;

class TourController extends Controller
{
    protected $tourRepository;

    public function __construct(TourRepositoryInterface $tourRepository)
    {
        $this->tourRepository = $tourRepository;
    }

    public function index()
    {
        // see all tours
        $tours = $this->tourRepository->getAll();

        return response()->json([
            'status' => 'success',
            'data' => $tours
        ]);
    }

    public function destroy(int $id)
    {
        // admin can deletee any tour 
        $this->tourRepository->delete($id);

        return response()->json([
            'status' => 'success',
            'message' => 'Tour deleted successfully'
        ]);
    }
}
