<?php

namespace App\Mail;

use App\Models\TripReport;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Mail\Mailable;
use Illuminate\Mail\Mailables\Content;
use Illuminate\Mail\Mailables\Envelope;
use Illuminate\Queue\SerializesModels;

class TripReportStatusMail extends Mailable implements ShouldQueue
{
    use Queueable, SerializesModels;

    public $report;

    public function __construct(TripReport $report)
    {
        $this->report = $report;
    }

    public function envelope(): Envelope
    {
        return new Envelope(
            subject: 'Update on your Kasbah Trek Trip Report',
        );
    }

    public function content(): Content
    {
        return new Content(
            markdown: 'emails.tripreport.status',
        );
    }

    public function attachments(): array
    {
        return [];
    }
}
