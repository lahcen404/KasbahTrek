<?php

namespace App\Repositories;

use App\Interfaces\AdminStatisticRepositoryInterface;
use App\Models\User;
use App\Models\Tour;
use App\Models\Booking;
use App\Models\Verification;
use App\Models\TripReport;
use App\Enums\UserRole;
use App\Enums\BookingStatus;
use App\Enums\Status;

class AdminStatisticRepository implements AdminStatisticRepositoryInterface
{
    public function getDashboardStats(): array
    {
        return [
            'total_travelers' => User::where('role', UserRole::TRAVELER)->count(),
            'total_guides' => User::where('role', UserRole::GUIDE)->count(),
            'total_tours' => Tour::count(),
            'total_revenue' => Booking::where('status', BookingStatus::CONFIRMED)->sum('total_price'),
            'total_confirmed_bookings' => Booking::where('status', BookingStatus::CONFIRMED)->count(),
            'pending_verifications' => Verification::where('status', Status::PENDING)->count(),
            'pending_trip_reports' => TripReport::where('status', Status::PENDING)->count(),
        ];
    }
}
