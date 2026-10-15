<?php

namespace Database\Factories;

use App\Enums\OfferStatus;
use App\Models\Fixture;
use App\Models\Offer;
use App\Models\Sector;
use App\Models\User;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Offer>
 */
class OfferFactory extends Factory
{
    /**
     * Define the model's default state.
     *
     * @return array<string, mixed>
     */
    public function definition(): array
    {
        return [
            'fixture_id' => Fixture::factory(),
            'user_id' => User::factory(),
            'sector_id' => Sector::factory(),
            'seats' => 1,
            'status' => OfferStatus::Open,
        ];
    }
}
