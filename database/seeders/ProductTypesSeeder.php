<?php

namespace Database\Seeders;

use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductTypesSeeder extends Seeder
{
    public function run(): void
    {
        $types = [
            [
                'code'        => 'battery',
                'name'        => 'Baterie',
                'description' => 'Náhradní baterie pro notebooky a přenosná zařízení.',
                'is_active'   => true,
            ],
            [
                'code'        => 'charger',
                'name'        => 'Nabíječka',
                'description' => 'Síťové adaptéry a nabíječky pro notebooky.',
                'is_active'   => true,
            ],
            [
                'code'        => 'adapter',
                'name'        => 'Adaptér',
                'description' => 'Konverzní a napájecí adaptéry.',
                'is_active'   => true,
            ],
            [
                'code'        => 'battery_kit',
                'name'        => 'Set baterie + nabíječka',
                'description' => 'Sada obsahující baterii i nabíječku pro daný model.',
                'is_active'   => true,
            ],
        ];

        foreach ($types as $data) {
            ProductType::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('ProductTypesSeeder: ' . count($types) . ' typů produktů.');
    }
}
