<?php

namespace App\Listeners;

use App\Events\BookingPaymentReceived;
use App\Mail\BookingPaymentReceivedMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendBookingPaymentReceivedNotification implements ShouldQueue
{
    public function handle(BookingPaymentReceived $event): void
    {
        $booking = $event->booking->loadMissing('traveler', 'tour');

        Mail::to($booking->traveler->email)
            ->send(new BookingPaymentReceivedMail($booking));
    }
}
