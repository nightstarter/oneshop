<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CompatibilityModel;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class ModelSeoLandingSeeder extends Seeder
{
    public function run(): void
    {
        $category = Category::query()->updateOrCreate(
            ['slug' => 'baterie-a-nabijecky-notebooky'],
            [
                'name' => 'Baterie a nabíječky pro notebooky',
                'description' => 'Kompatibilní baterie a nabíječky pro notebooky.',
                'sort_order' => 10,
                'is_active' => true,
            ]
        );

        $batteryItem = StockItem::query()->where('sku', 'BAT-6C-ASUS-X555')->first();
        $chargerItem = StockItem::query()->where('sku', 'CHG-65W-ASUS-4.0')->first();

        if ($batteryItem === null || $chargerItem === null) {
            return;
        }

        $landingDefinitions = [
            [
                'sku' => 'SEO-BAT-ASUS-X555L-BLACK',
                'name' => 'Baterie pro Asus X555L (2026 edition)',
                'model' => 'X555L',
                'stock_item_id' => $batteryItem->id,
                'price' => 1290.00,
            ],
            [
                'sku' => 'SEO-BAT-ASUS-X555LA-LONG',
                'name' => 'Baterie Asus X555LA - prodloužená výdrž',
                'model' => 'X555LA',
                'stock_item_id' => $batteryItem->id,
                'price' => 1290.00,
            ],
            [
                'sku' => 'SEO-BAT-ASUS-X555LD-ALTERNATIVE',
                'name' => 'Náhradní baterie pro Asus X555LD',
                'model' => 'X555LD',
                'stock_item_id' => $batteryItem->id,
                'price' => 1290.00,
            ],
            [
                'sku' => 'SEO-CHG-ASUS-X555L-FAST',
                'name' => 'Rychlá nabíječka pro Asus X555L 65W',
                'model' => 'X555L',
                'stock_item_id' => $chargerItem->id,
                'price' => 590.00,
            ],
            [
                'sku' => 'SEO-CHG-ASUS-X555LA-ORIGINAL',
                'name' => 'Nabíječka pro Asus X555LA (kompatibilní)',
                'model' => 'X555LA',
                'stock_item_id' => $chargerItem->id,
                'price' => 590.00,
            ],
        ];

        foreach ($landingDefinitions as $definition) {
            $model = $this->upsertCompatibilityModel('Asus', $definition['model']);

            $product = Product::query()->updateOrCreate(
                ['sku' => $definition['sku']],
                [
                    'name' => $definition['name'],
                    'slug' => Str::slug($definition['name']),
                    'description' => $this->buildSeoDescription($definition['name'], $definition['model']),
                    'stock_item_id' => $definition['stock_item_id'],
                    'price' => $definition['price'],
                    'active' => true,
                    'visibility' => 'public',
                    // Backward compatibility for existing pricing/stock logic.
                    'base_price_net' => $definition['price'],
                    'stock_qty' => 0,
                    'is_active' => true,
                ]
            );

            $product->categories()->syncWithoutDetaching([$category->id]);
            $product->compatibilityModels()->syncWithoutDetaching([$model->id]);
        }
    }

    private function upsertCompatibilityModel(string $brand, string $modelName): CompatibilityModel
    {
        return CompatibilityModel::query()->updateOrCreate(
            [
                'brand' => $brand,
                'model_name' => $modelName,
            ],
            [
                'model_code' => $modelName,
                'slug' => Str::slug($brand . ' ' . $modelName),
                'active' => true,
            ]
        );
    }

    private function buildSeoDescription(string $title, string $model): string
    {
        return implode("\n\n", [
            $title,
            "SEO landing stránka zaměřená na model {$model}.",
            "Tato karta sdílí stejnou fyzickou skladovou položku přes stock_item_id, takže nedochází k duplikaci skladu.",
            'Obsah je připravený pro dlouhá klíčová slova a modelové dotazy ve vyhledávačích.',
        ]);
    }
}
