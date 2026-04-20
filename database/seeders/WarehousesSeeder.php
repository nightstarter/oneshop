<?php

namespace Database\Seeders;

use App\Models\Warehouse;
use Illuminate\Database\Seeder;

class WarehousesSeeder extends Seeder
{
    public function run(): void
    {
        $warehouses = [
            [
                'code'      => 'MAIN',
                'name'      => 'Hlavní sklad',
                'is_sale'   => false,
                'is_active' => true,
                'priority'  => 10,
            ],
            [
                'code'      => 'BERLIN',
                'name'      => 'Sklad Berlín',
                'is_sale'   => false,
                'is_active' => true,
                'priority'  => 20,
            ],
            [
                'code'      => 'SALE',
                'name'      => 'Výprodej',
                'is_sale'   => true,
                'is_active' => true,
                'priority'  => 30,
            ],
            [
                'code'      => 'VIRTUAL',
                'name'      => 'Virtuální / na objednávku',
                'is_sale'   => false,
                'is_active' => false,
                'priority'  => 99,
            ],
        ];

        foreach ($warehouses as $data) {
            Warehouse::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('WarehousesSeeder: ' . count($warehouses) . ' skladů.');
    }
}
