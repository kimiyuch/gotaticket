<?php

namespace Database\Seeders;

use App\Enums\SectorKind;
use App\Models\Sector;
use Illuminate\Database\Seeder;

class SectorSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $sectors = [
            ['code' => 'A', 'name' => 'Sitzplatz Estrade', 'kind' => SectorKind::Seated],
            ['code' => 'B', 'name' => 'Stehplatz Bierkurve', 'kind' => SectorKind::Standing],
            ['code' => 'C', 'name' => 'Stehplatz Osttribüne', 'kind' => SectorKind::Standing],
            ['code' => 'D2', 'name' => 'Stehplatz Eulachkurve', 'kind' => SectorKind::Standing],
            ['code' => null, 'name' => 'Sitzplatz Haupttribüne', 'kind' => SectorKind::Seated],
            ['code' => null, 'name' => 'Stehplatz Sirupkurve', 'kind' => SectorKind::Standing],
            ['code' => null, 'name' => 'Stehplatz Klubhaus', 'kind' => SectorKind::Standing],
        ];

        foreach ($sectors as $sort => $sector) {
            Sector::updateOrCreate(['name' => $sector['name']], [...$sector, 'sort' => $sort + 1]);
        }
    }
}
