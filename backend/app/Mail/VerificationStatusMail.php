<?php

namespace App\Mail;

use App\Models\Verification;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class VerificationStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $verification;

    public function __construct(Verification $verification)
    {
        $this->verification = $verification;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your Guide Verification Status',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.verification.status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
