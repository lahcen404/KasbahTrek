<?php

namespace App\Listeners;

use App\Events\BookingStatusUpdated;
use App\Mail\BookingStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingStatusNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(BookingStatusUpdated $event): void
    {
        Mail::to($event->booking->traveler->email)
            ->send(new BookingStatusMail($event->booking));
    }
}
