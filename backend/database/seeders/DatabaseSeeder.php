<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {
        
        User::create([
            'fullname' => 'Lahcen AIT MASKOUR',
            'email' => 'lahcen.maskour2003@gmail.com',
            'password' => Hash::make('lahcen123'),
            'role' => UserRole::ADMIN,
        ]);

        // create 3 Guides and 10 Tours
        $guides = User::factory(3)->create(['role' => UserRole::GUIDE]);
        foreach ($guides as $guide) {
            Tour::factory(3)->create(['guide_id' => $guide->id]);
        }

        // create 5 Travelers and some Bookings
        $travelers = User::factory(5)->create(['role' => UserRole::TRAVELER]);
        foreach ($travelers as $traveler) {
            Booking::factory(2)->create([
                'traveler_id' => $traveler->id,
                'tour_id' => Tour::inRandomOrder()->first()->id,
            ]);
        }
    }
}
