<?php
namespace App\Enums;

enum BookingStatus: string {
    case PENDING = 'PENDING';
    case CONFIRMED = 'CONFIRMED';
    case REJECTED = 'REJECTED';
    case CANCELLED = 'CANCELLED';
}
