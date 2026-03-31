<?php

namespace App\Events;

use App\Models\Verification;
use Illuminate\Broadcasting\InteractsWithSockets;
use Illuminate\Foundation\Events\Dispatchable;
use Illuminate\Queue\SerializesModels;

class VerificationStatusUpdated
{
    use Dispatchable, InteractsWithSockets, SerializesModels;

    public $verification;

    public function __construct(Verification $verification)
    {
        $this->verification = $verification;
    }
}
