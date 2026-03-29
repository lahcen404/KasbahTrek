<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Http\Requests\Api\TripReport\StoreTripReportRequest;
use App\Interfaces\TripReportRepositoryInterface;

class TripReportController extends Controller
{
    protected $tripReportRepository;

    public function __construct(TripReportRepositoryInterface $tripReportRepository)
    {
        $this->tripReportRepository = $tripReportRepository;
    }

    public function store(StoreTripReportRequest $request)
    {
        $data = $request->validated();
        
        $data['status'] = 'PENDING';
        $data['traveler_id'] = auth()->id();

        $report = $this->tripReportRepository->create($data);

        return response()->json([
            'message' => 'Trip report submitted successfully !!!',
            'report' => $report,
        ], 201);
    }
}
