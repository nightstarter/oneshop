<?php

namespace Database\Seeders;

use App\Models\AttributeProductType;
use App\Models\ProductAttribute;
use App\Models\ProductType;
use Illuminate\Database\Seeder;

class ProductAttributesSeeder extends Seeder
{
    /**
     * Master attribute catalogue.
     *
     * 'code'         → unique identifier used in code (never rename after import)
     * 'data_type'    → text | number | boolean | json
     * 'unit'         → optional display unit
     * 'is_filterable'→ show in faceted filter sidebar
     * 'sort_order'   → global default ordering
     * 'types'        → which product_type codes get this attribute
     *                  [type_code => ['is_required', 'is_filterable', 'sort_order']]
     */
    private array $attributes = [
        // ── Shared ────────────────────────────────────────────────────────
        [
            'code'         => 'ean',
            'name'         => 'EAN',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> false,
            'sort_order'   => 5,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 5],
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 5],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 5],
                'battery_kit' => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 5],
            ],
        ],
        [
            'code'         => 'manufacturer',
            'name'         => 'Výrobce',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 10,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 10],
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 10],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 10],
                'battery_kit' => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 10],
            ],
        ],
        [
            'code'         => 'color',
            'name'         => 'Barva',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 15,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 15],
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 15],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 15],
            ],
        ],
        [
            'code'         => 'weight_g',
            'name'         => 'Hmotnost',
            'data_type'    => 'number',
            'unit'         => 'g',
            'is_filterable'=> false,
            'sort_order'   => 20,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 20],
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 20],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 20],
            ],
        ],
        [
            'code'         => 'is_original',
            'name'         => 'Originální díl',
            'data_type'    => 'boolean',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 25,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 25],
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 25],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 25],
            ],
        ],
        [
            'code'         => 'catalog_number',
            'name'         => 'Katalogové číslo',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> false,
            'sort_order'   => 30,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 30],
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 30],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 30],
            ],
        ],
        // ── Battery-specific ──────────────────────────────────────────────
        [
            'code'         => 'capacity_mah',
            'name'         => 'Kapacita',
            'data_type'    => 'number',
            'unit'         => 'mAh',
            'is_filterable'=> true,
            'sort_order'   => 100,
            'types'        => [
                'battery'     => ['is_required' => true,  'is_filterable' => true, 'sort_order' => 100],
                'battery_kit' => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 100],
            ],
        ],
        [
            'code'         => 'voltage_v',
            'name'         => 'Napětí',
            'data_type'    => 'number',
            'unit'         => 'V',
            'is_filterable'=> true,
            'sort_order'   => 110,
            'types'        => [
                'battery'     => ['is_required' => true,  'is_filterable' => true, 'sort_order' => 110],
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 110],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 110],
                'battery_kit' => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 110],
            ],
        ],
        [
            'code'         => 'cell_count',
            'name'         => 'Počet článků',
            'data_type'    => 'number',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 120,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 120],
            ],
        ],
        [
            'code'         => 'chemistry',
            'name'         => 'Chemie článků',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 130,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 130],
                'battery_kit' => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 130],
            ],
        ],
        // ── Charger / Adapter specific ────────────────────────────────────
        [
            'code'         => 'max_power_w',
            'name'         => 'Maximální výkon',
            'data_type'    => 'number',
            'unit'         => 'W',
            'is_filterable'=> true,
            'sort_order'   => 200,
            'types'        => [
                'charger'     => ['is_required' => true,  'is_filterable' => true, 'sort_order' => 200],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 200],
                'battery_kit' => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 200],
            ],
        ],
        [
            'code'         => 'current_a',
            'name'         => 'Proud',
            'data_type'    => 'number',
            'unit'         => 'A',
            'is_filterable'=> false,
            'sort_order'   => 210,
            'types'        => [
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 210],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 210],
            ],
        ],
        [
            'code'         => 'plug_type',
            'name'         => 'Konektor / zástrčka',
            'data_type'    => 'text',
            'unit'         => null,
            'is_filterable'=> true,
            'sort_order'   => 220,
            'types'        => [
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 220],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 220],
                'battery_kit' => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 220],
            ],
        ],
        [
            'code'         => 'connector_size_mm',
            'name'         => 'Rozměr konektoru',
            'data_type'    => 'text',
            'unit'         => 'mm',
            'is_filterable'=> true,
            'sort_order'   => 230,
            'types'        => [
                'charger'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 230],
                'adapter'     => ['is_required' => false, 'is_filterable' => true, 'sort_order' => 230],
            ],
        ],
        // ── Import / SEO helpers ──────────────────────────────────────────
        [
            'code'         => 'lead_time_days',
            'name'         => 'Dodací lhůta',
            'data_type'    => 'number',
            'unit'         => 'dní',
            'is_filterable'=> false,
            'sort_order'   => 300,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 300],
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 300],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 300],
            ],
        ],
        [
            'code'         => 'on_request',
            'name'         => 'Na dotaz',
            'data_type'    => 'boolean',
            'unit'         => null,
            'is_filterable'=> false,
            'sort_order'   => 310,
            'types'        => [
                'battery'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 310],
                'charger'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 310],
                'adapter'     => ['is_required' => false, 'is_filterable' => false, 'sort_order' => 310],
            ],
        ],
    ];

    public function run(): void
    {
        $attrCount  = 0;
        $pivotCount = 0;

        foreach ($this->attributes as $data) {
            $types = $data['types'] ?? [];
            unset($data['types']);

            $attr = ProductAttribute::updateOrCreate(
                ['code' => $data['code']],
                $data,
            );
            $attrCount++;

            foreach ($types as $typeCode => $pivotData) {
                $type = ProductType::where('code', $typeCode)->first();
                if (! $type) {
                    $this->command->warn("ProductAttributesSeeder: ProductType '{$typeCode}' nenalezen, přeskakuji vazbu pro '{$data['code']}'.");
                    continue;
                }

                AttributeProductType::updateOrCreate(
                    ['attribute_id' => $attr->id, 'product_type_id' => $type->id],
                    $pivotData,
                );
                $pivotCount++;
            }
        }

        $this->command->info("ProductAttributesSeeder: {$attrCount} atributů, {$pivotCount} vazeb typ↔atribut.");
    }
}
