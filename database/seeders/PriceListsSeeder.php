<?php

namespace Database\Seeders;

use App\Models\PriceList;
use Illuminate\Database\Seeder;

class PriceListsSeeder extends Seeder
{
    public function run(): void
    {
        $lists = [
            // Mapa: code => display data
            // Odpovídá legacy Cena1..Cena8 sloupcům:
            //   DEFAULT → Cena1 (veřejný maloobchod)
            //   B2B     → Cena2 (registrovaný B2B zákazník)
            //   VIP     → Cena5 (VIP / stálý zákazník)
            //   RESELLER→ Cena6 (autorizovaný prodejce)
            //   BULK    → Cena7 (množstevní nákup)
            //   PARTNER → Cena8 (partnerský program)
            [
                'code'        => 'DEFAULT',
                'name'        => 'Maloobchod',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => true,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
            [
                'code'        => 'B2B',
                'name'        => 'B2B zákazník',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => true,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
            [
                'code'        => 'VIP',
                'name'        => 'VIP zákazník',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => true,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
            [
                'code'        => 'RESELLER',
                'name'        => 'Autorizovaný prodejce',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => true,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
            [
                'code'        => 'BULK',
                'name'        => 'Množstevní nákup',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => true,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
            [
                'code'        => 'PARTNER',
                'name'        => 'Partnerský program',
                'currency'    => config('shop.currency', 'CZK'),
                'is_active'   => false,
                'valid_from'  => null,
                'valid_to'    => null,
            ],
        ];

        foreach ($lists as $data) {
            PriceList::updateOrCreate(['code' => $data['code']], $data);
        }

        $this->command->info('PriceListsSeeder: ' . count($lists) . ' ceníků.');
    }
}
