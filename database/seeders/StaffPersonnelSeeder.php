<?php

namespace Database\Seeders;

use App\Models\StaffPersonnel;
use Illuminate\Database\Seeder;

class StaffPersonnelSeeder extends Seeder
{
    public function run(): void
    {
        // Initial ICTS personnel master list.
        // staff_id 43 is the existing ICTS staff record.
        $personnel = [
            [
                'name' => 'Marlon',
                'position' => null,
            ],
            [
                'name' => 'Lander',
                'position' => null,
            ],
            [
                'name' => 'Teddy',
                'position' => null,
            ],
            [
                'name' => 'teds',
                'position' => null,
            ],
            [
                'name' => 'ted',
                'position' => null,
            ],
        ];

        foreach ($personnel as $person) {
            StaffPersonnel::updateOrCreate(
                [
                    'staff_id' => 43,
                    'name' => $person['name'],
                ],
                [
                    'position' => $person['position'],
                    'is_active' => true,
                ]
            );
        }
    }
}
