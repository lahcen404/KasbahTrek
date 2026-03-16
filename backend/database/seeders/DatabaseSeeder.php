<?php

namespace Database\Seeders;

use App\Enums\UserRole;
use App\Models\Booking;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder {
    public function run(): void {

        // admin Account
        User::create([
            'fullname' => 'Lahcen AIT MASKOUR',
            'email' => 'lahcen.maskour2003@gmail.com',
            'password' => Hash::make('lahcen123'),
            'role' => UserRole::ADMIN,
        ]);

        // guide account
        $myGuide = User::create([
            'fullname' => 'Lahcen Guide',
            'email' => 'lahcen.maskour@gmail.com',
            'password' => Hash::make('lahcen123'),
            'role' => UserRole::GUIDE,
            'is_verified' => true,
        ]);

        // traveler acccount
        $myTraveler = User::create([
            'fullname' => 'Lahcen Traveler',
            'email' => 'lahcen@gmail.com',
            'password' => Hash::make('lahcen123'),
            'role' => UserRole::TRAVELER,
        ]);

        // create 3 more guides with tours
        $guides = User::factory(3)->create(['role' => UserRole::GUIDE]);

        // create tours for specific guide
        Tour::factory(2)->create(['guide_id' => $myGuide->id]);

        foreach ($guides as $guide) {
            Tour::factory(3)->create(['guide_id' => $guide->id]);
        }

        // create 5 Travelers and 2 Bookings for each
        $travelers = User::factory(5)->create(['role' => UserRole::TRAVELER]);

        // book a tour for specific Traveler
        Booking::factory()->create([
            'traveler_id' => $myTraveler->id,
            'tour_id' => Tour::inRandomOrder()->first()->id,
            'guide_id' => $myGuide->id,
        ]);

        foreach ($travelers as $traveler) {
            Booking::factory(2)->create([
                'traveler_id' => $traveler->id,
                'tour_id' => Tour::inRandomOrder()->first()->id,
                'guide_id' => User::where('role', UserRole::GUIDE)->inRandomOrder()->first()->id,
            ]);
        }
    }
}
