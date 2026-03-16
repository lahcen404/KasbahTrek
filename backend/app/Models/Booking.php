<?php

namespace App\Models;

use App\Enums\BookingStatus;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Booking extends Model
{
    use HasFactory;

    protected $fillable = ['date', 'total_price', 'status', 'traveler_id', 'tour_id', 'guide_id'];

    protected $casts = [
        'status' => BookingStatus::class,
        'date' => 'date',
    ];

    public function traveler() { return $this->belongsTo(User::class, 'traveler_id'); }
    public function tour() { return $this->belongsTo(Tour::class); }
    public function guide() { return $this->belongsTo(User::class, 'guide_id'); }
}
