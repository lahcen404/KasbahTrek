<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;

use App\Enums\UserRole;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use  HasFactory;

    protected $fillable = ['fullname', 'email', 'password', 'role', 'is_verified'];

    protected $hidden = ['password', 'remember_token'];

    protected $casts = [
        'role' => UserRole::class,
        'is_verified' => 'boolean',
        'password' => 'hashed',
    ];

    // Guide relations
    public function tours() {
        return $this->hasMany(Tour::class, 'guide_id');
    }
    public function verificationRequest() {
        return $this->hasOne(Verification::class, 'guide_id');
    }
    public function receivedBookings() {
        return $this->hasMany(Booking::class, 'guide_id');
    }

    // Traveler relations
    public function bookings() {
        return $this->hasMany(Booking::class, 'traveler_id');
    }
    public function reviews() {
        return $this->hasMany(Review::class, 'traveler_id');
    }
    public function favorites() {
        return $this->hasMany(Favorite::class, 'traveler_id');
    }

    public function reports() {
        return $this->hasMany(TripReport::class, 'traveler_id');
    }

    //Admin relations
    public function approvedVerifications() {
        return $this->hasMany(Verification::class, 'admin_id');
    }
    public function managedReports() {
        return $this->hasMany(TripReport::class, 'admin_id');
    }
}
