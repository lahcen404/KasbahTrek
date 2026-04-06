<?php

namespace App\Models;

use App\Enums\BookingStatus;
use App\Enums\PaymentStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'total_price', 'status', 'traveler_id', 'tour_id', 'guide_id', 'reminder_sent_at', 'payment_status', 'paid_at', 'payment_receipt_sent_at', 'paypal_order_id'];

    protected $casts = [
        'status' => BookingStatus::class,
        'date' => 'date',
        'reminder_sent_at' => 'datetime',
        'payment_status' => PaymentStatus::class,
        'paid_at' => 'datetime',
        'payment_receipt_sent_at' => 'datetime',
    ];

    public function traveler()
    {
        return $this->belongsTo(User::class, 'traveler_id');
    }

    public function tour()
    {
        return $this->belongsTo(Tour::class);
    }

    public function guide()
    {
        return $this->belongsTo(User::class, 'guide_id');
    }
}
