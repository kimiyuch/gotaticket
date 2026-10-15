<?php

namespace Database\Factories;

use App\Enums\SectorKind;
use App\Models\Sector;
use Illuminate\Database\Eloquent\Factories\Factory;

/**
 * @extends Factory<Sector>
 */
class SectorFactory extends Factory
{
	/**
	 * Define the model's default state.
	 *
	 * @return array<string, mixed>
	 */
	public function definition(): array
	{
		return [
			'code' => fake()->unique()->bothify('?#'),
			'name' => 'Stehplatz '.fake()->unique()->word(),
			'kind' => SectorKind::Standing,
			'sort' => fake()->numberBetween(1, 100),
			'is_active' => true,
		];
	}
}
