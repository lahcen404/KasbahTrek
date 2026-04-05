<?php

namespace App\Console\Commands;

use App\Enums\BookingStatus;
use App\Mail\BookingReminderMail;
use App\Models\Booking;
use Illuminate\Console\Command;
use Illuminate\Support\Carbon;
use Illuminate\Support\Facades\Mail;

class SendBookingReminders extends Command
{
    protected $signature = 'bookings:send-reminders
                            {--tour-date= : For testing only: Y-m-d — remind for bookings on this tour date (instead of "tomorrow")}';

    protected $description = 'Queue reminder emails for confirmed bookings whose tour date is tomorrow (app timezone), or use --tour-date for testing.';

    public function handle(): int
    {
        $tz = config('app.timezone');
        $raw = $this->option('tour-date');

        if ($raw !== null && $raw !== '') {
            if (! preg_match('/^\d{4}-\d{2}-\d{2}$/', $raw)) {
                $this->error('Invalid --tour-date. Use Y-m-d (e.g. 2026-04-10).');

                return self::FAILURE;
            }
            $tourDay = $raw;
            $this->warn("TEST MODE: selecting bookings with tour date {$tourDay} (ignoring calendar \"tomorrow\").");
        } else {
            $tourDay = Carbon::now($tz)->addDay()->toDateString();
        }

        $bookings = Booking::query()
            ->where('status', BookingStatus::CONFIRMED)
            ->whereDate('date', $tourDay)
            ->whereNull('reminder_sent_at')
            ->with(['traveler', 'tour'])
            ->get();

        $count = 0;

        foreach ($bookings as $booking) {
            if (! $booking->traveler?->email || ! $booking->tour) {
                continue;
            }

            Mail::to($booking->traveler->email)->queue(new BookingReminderMail($booking));
            $booking->update(['reminder_sent_at' => now()]);
            $count++;
        }

        $this->info("Queued {$count} booking reminder(s) for tours on {$tourDay}.");

        return self::SUCCESS;
    }
}
