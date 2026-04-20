<?php

namespace App\Enums;

enum UserRole: string {
    case TRAVELER = 'TRAVELER';
    case GUIDE = 'GUIDE';
    case ADMIN = 'ADMIN';
}
