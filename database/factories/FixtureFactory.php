<?php

namespace Database\Factories;

use App\Models\Fixture;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Fixture>
 */
class FixtureFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'opponent' => fake()->randomElement(['SC Kriens', 'FC Aarau', 'FC Vaduz', 'Neuchâtel Xamax', 'FC Wil', 'FC Stade Nyonnais']),
            'kickoff_at' => fake()->dateTimeBetween('+1 day', '+3 months')->setTime(18, 0),
            'competition' => 'Challenge League',
        ];
    }
}
