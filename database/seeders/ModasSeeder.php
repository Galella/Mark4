<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\Moda;

class ModasSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Create default modas
        $modas = [
            [
                'name' => 'ONS TENGAH',
                'description' => 'Operational Nusantara Selatan Tengah'
            ],
            [
                'name' => 'ONS UTARA',
                'description' => 'Operational Nusantara Selatan Utara'
            ],
            [
                'name' => 'ONS SELATAN',
                'description' => 'Operational Nusantara Selatan Selatan'
            ],
            [
                'name' => 'ONES',
                'description' => 'Operational Nusantara Eastern Side'
            ],
            [
                'name' => 'ONWS',
                'description' => 'Operational Nusantara Western Side'
            ]
        ];

        foreach ($modas as $moda) {
            Moda::firstOrCreate([
                'name' => $moda['name']
            ], [
                'name' => $moda['name'],
                'description' => $moda['description']
            ]);
        }
    }
}