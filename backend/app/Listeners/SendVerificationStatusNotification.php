<?php

namespace App\Listeners;

use App\Events\VerificationStatusUpdated;
use App\Mail\VerificationStatusMail;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Support\Facades\Mail;

class SendVerificationStatusNotification implements ShouldQueue
{
    public function __construct()
    {
        //
    }

    public function handle(VerificationStatusUpdated $event): void
    {
        Mail::to($event->verification->guide->email)
            ->send(new VerificationStatusMail($event->verification));
    }
}
