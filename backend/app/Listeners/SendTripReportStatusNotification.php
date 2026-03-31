<?php

namespace App\Listeners;

use App\Events\TripReportStatusUpdated;
use App\Mail\TripReportStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendTripReportStatusNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(TripReportStatusUpdated $event): void
    {
        Mail::to($event->report->traveler->email)
            ->send(new TripReportStatusMail($event->report));
    }
}
