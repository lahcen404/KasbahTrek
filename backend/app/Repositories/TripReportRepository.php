<?php

namespace App\Repositories;

use App\Interfaces\TripReportRepositoryInterface;
use App\Models\TripReport;

class TripReportRepository implements TripReportRepositoryInterface
{
    public function create(array $data)
    {
        return TripReport::create($data);
    }

    public function getAll()
    {
        return TripReport::with(['traveler', 'tour', 'admin'])->get();
    }

    public function updateStatus(int $id, string $status)
    {
        $report = TripReport::findOrFail($id);
        
        $report->update([
            'status' => $status,
            'admin_id' => auth()->id()
        ]);
        
        return $report;
    }
}
