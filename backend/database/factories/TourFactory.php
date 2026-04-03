<?php

namespace Database\Factories;

use App\Enums\DifficultyLevel;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends \Illuminate\Database\Eloquent\Factories\Factory<\App\Models\Tour>
 */
class TourFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array {
    return [
        'title' => fake()->randomElement(['Atlas Trek', 'Desert Night', 'Kasbah Discovery', 'Forest Hike']),
        'description' => fake()->paragraph(),
        'location' => fake()->randomElement(['Beni Mellal', 'Azilal', 'Khenifra', 'Ifrane']),
        'price' => fake()->randomFloat(2, 100, 2000),
        'difficulty' => fake()->randomElement(DifficultyLevel::cases()),
        'max_spots' => fake()->numberBetween(5, 20),
        'duration_hours' => fake()->numberBetween(2, 48),
        'current_bookings' => 0,
        'guide_id' => User::factory(),
    ];
}
}
