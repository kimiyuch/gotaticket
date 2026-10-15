<?php

namespace Database\Seeders;

use App\Models\Fixture;
use Illuminate\Database\Seeder;

class FixtureSeeder extends Seeder
{
    /**
     * Home fixtures 2026/27 until the winter break, as published on
     * tickets.fcwinterthur.ch and in the SFL Challenge League schedule.
     */
    public function run(): void
    {
        $fixtures = [
            ['opponent' => 'FC Luzern', 'kickoff_at' => '2026-10-17 18:00', 'competition' => 'Schweizer Cup'],
            ['opponent' => 'FC Stade Nyonnais', 'kickoff_at' => '2026-10-23 20:15', 'competition' => 'Challenge League'],
            ['opponent' => 'Etoile Carouge FC', 'kickoff_at' => '2026-11-07 18:00', 'competition' => 'Challenge League'],
            ['opponent' => 'FC Rapperswil-Jona', 'kickoff_at' => '2026-11-27 20:15', 'competition' => 'Challenge League'],
            ['opponent' => 'SC Kriens', 'kickoff_at' => '2026-12-12 18:00', 'competition' => 'Challenge League'],
        ];

        foreach ($fixtures as $fixture) {
            Fixture::updateOrCreate(
                ['opponent' => $fixture['opponent'], 'kickoff_at' => $fixture['kickoff_at']],
                $fixture,
            );
        }
    }
}
