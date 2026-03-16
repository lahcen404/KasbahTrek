<?php

namespace Database\Factories;

use App\Enums\BookingStatus;
use App\Models\Tour;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Booking>
 */
class BookingFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
   public function definition(): array {
    return [
        'date' => fake()->dateTimeBetween('now', '+1 month'),
        'total_price' => fake()->randomFloat(2, 200, 1000),
        'status' => BookingStatus::PENDING,
        'traveler_id' => User::factory(),
        'tour_id' => Tour::factory(),
    ];
}
}
