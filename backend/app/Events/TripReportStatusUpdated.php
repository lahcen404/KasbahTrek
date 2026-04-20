<?php

namespace App\Events;

use App\Models\TripReport;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class TripReportStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $report;

    public function __construct(TripReport $report)
    {
        $this->report = $report;
    }
}
