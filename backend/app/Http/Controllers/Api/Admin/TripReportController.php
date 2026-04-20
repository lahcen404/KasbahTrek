<?php

namespace App\Http\Controllers\Api\Admin;

use App\Http\Controllers\Controller;
use App\Interfaces\TripReportRepositoryInterface;
use Illuminate\Http\Request;

class TripReportController extends Controller
{
    protected $tripReportRepository;

    public function __construct(TripReportRepositoryInterface $tripReportRepository)
    {
        $this->tripReportRepository = $tripReportRepository;
    }

    public function index()
    {
        $reports = $this->tripReportRepository->getAll();

        return response()->json($reports);
    }

    public function updateStatus(Request $request, int $id)
    {
        $validated = $request->validate([
            'status' => 'required|string|in:PENDING,APPROVED,REJECTED,RESOLVED'
        ]);

        $report = $this->tripReportRepository->updateStatus($id, $validated['status']);

        // diiispatch event to trigger the email listener
        event(new \App\Events\TripReportStatusUpdated($report));

        return response()->json([
            'message' => 'Trip report status updated successfully !!!',
            'report' => $report,
        ]);
    }
}
