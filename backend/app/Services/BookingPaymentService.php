<?php

namespace App\Services;

use App\Enums\PaymentStatus;
use App\Mail\BookingPaymentReceivedMail;
use App\Models\Booking;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Mail;

class BookingPaymentService
{
    public function markPaidAndSendReceipt(Booking $booking): void
    {
        if ($booking->payment_status !== PaymentStatus::PAID) {
            $booking->update([
                'payment_status' => PaymentStatus::PAID,
                'paid_at' => now(),
            ]);
        }

        $booking->refresh()->load(['traveler', 'tour']);

        $this->sendReceiptIfNeeded($booking);
    }

    public function sendReceiptIfNeeded(Booking $booking): void
    {
        if ($booking->payment_receipt_sent_at !== null) {
            return;
        }

        if ($booking->payment_status !== PaymentStatus::PAID) {
            return;
        }

        try {
            $booking->loadMissing('traveler', 'tour');

            Mail::to($booking->traveler->email)
                ->send(new BookingPaymentReceivedMail($booking));

            $booking->forceFill(['payment_receipt_sent_at' => now()])->save();
        } catch (\Throwable $e) {
            Log::error('Payment receipt email failed', [
                'booking_id' => $booking->id,
                'exception' => $e->getMessage(),
            ]);
        }
    }
}
