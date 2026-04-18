<?php

namespace Database\Seeders;

use App\Models\Category;
use App\Models\CompatibilityModel;
use App\Models\Product;
use App\Models\StockItem;
use Illuminate\Database\Seeder;
use Illuminate\Support\Str;

class SharedStockCatalogSeeder extends Seeder
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

        $batteryItem = StockItem::query()->updateOrCreate(
            ['sku' => 'BAT-6C-ASUS-X555'],
            [
                'name' => 'Li-Ion baterie 6-cell pro Asus X555 řadu',
                'product_type' => 'battery',
                'quantity' => 24,
                'purchase_price' => 650.00,
                'sale_price' => 1290.00,
                'ean' => '8591234567001',
                'active' => true,
            ]
        );

        $chargerItem = StockItem::query()->updateOrCreate(
            ['sku' => 'CHG-65W-ASUS-4.0'],
            [
                'name' => 'Nabíječka 65W 19V 3.42A, konektor 4.0x1.35',
                'product_type' => 'charger',
                'quantity' => 16,
                'purchase_price' => 290.00,
                'sale_price' => 590.00,
                'ean' => '8591234567002',
                'active' => true,
            ]
        );

        $kitItem = StockItem::query()->updateOrCreate(
            ['sku' => 'KIT-ASUS-X555-BAT-CHG'],
            [
                'name' => 'Set baterie + nabíječka pro Asus X555',
                'product_type' => 'battery_kit',
                'quantity' => 8,
                'purchase_price' => 920.00,
                'sale_price' => 1690.00,
                'ean' => '8591234567003',
                'active' => true,
            ]
        );

        $kitItem->componentItems()->sync([
            $batteryItem->id => ['quantity' => 1],
            $chargerItem->id => ['quantity' => 1],
        ]);

        $this->upsertProduct(
            category: $category,
            stockItem: $batteryItem,
            sku: 'BAT-ASUS-X555L',
            name: 'Baterie pro Asus X555L',
            description: 'Náhradní baterie kompatibilní s notebooky Asus X555L.',
            price: 1290.00,
        );

        $this->upsertProduct(
            category: $category,
            stockItem: $batteryItem,
            sku: 'BAT-ASUS-X555LA',
            name: 'Baterie pro Asus X555LA',
            description: 'Náhradní baterie kompatibilní s notebooky Asus X555LA.',
            price: 1290.00,
        );

        $this->upsertProduct(
            category: $category,
            stockItem: $batteryItem,
            sku: 'BAT-ASUS-X555LD',
            name: 'Baterie pro Asus X555LD',
            description: 'Náhradní baterie kompatibilní s notebooky Asus X555LD.',
            price: 1290.00,
        );

        $chargerProduct = $this->upsertProduct(
            category: $category,
            stockItem: $chargerItem,
            sku: 'CHG-ASUS-X555-65W',
            name: 'Nabíječka pro Asus X555 (65W)',
            description: 'Síťový adaptér 65W kompatibilní s řadou Asus X555.',
            price: 590.00,
        );

        $kitProduct = $this->upsertProduct(
            category: $category,
            stockItem: $kitItem,
            sku: 'SET-ASUS-X555-BAT-CHG',
            name: 'Set baterie + nabíječka pro Asus X555',
            description: 'Výhodná sada baterie a nabíječky pro Asus X555 modely.',
            price: 1690.00,
        );

        $batteryProducts = Product::query()
            ->whereIn('sku', ['BAT-ASUS-X555L', 'BAT-ASUS-X555LA', 'BAT-ASUS-X555LD'])
            ->get()
            ->keyBy('sku');

        $compatibilityMap = [
            'BAT-ASUS-X555L' => ['X555L', 'X555LD', 'X555LN'],
            'BAT-ASUS-X555LA' => ['X555LA', 'X555LAB', 'X555LJ'],
            'BAT-ASUS-X555LD' => ['X555LD', 'X555LDB', 'X555LP'],
        ];

        foreach ($compatibilityMap as $productSku => $models) {
            $product = $batteryProducts->get($productSku);
            if ($product === null) {
                continue;
            }

            $product->compatibilityModels()->sync(
                collect($models)
                    ->map(fn (string $model) => $this->upsertCompatibilityModel('Asus', $model)->id)
                    ->all()
            );
        }

        $commonModels = ['X555L', 'X555LA', 'X555LD', 'X555LN'];

        $chargerProduct->compatibilityModels()->sync(
            collect($commonModels)
                ->map(fn (string $model) => $this->upsertCompatibilityModel('Asus', $model)->id)
                ->all()
        );

        $kitProduct->compatibilityModels()->sync(
            collect($commonModels)
                ->map(fn (string $model) => $this->upsertCompatibilityModel('Asus', $model)->id)
                ->all()
        );
    }

    private function upsertProduct(
        Category $category,
        StockItem $stockItem,
        string $sku,
        string $name,
        string $description,
        float $price,
    ): Product {
        $product = Product::query()->updateOrCreate(
            ['sku' => $sku],
            [
                'name' => $name,
                'slug' => Str::slug($name),
                'description' => $description,
                'stock_item_id' => $stockItem->id,
                'price' => $price,
                'active' => true,
                'visibility' => 'public',
                // Backward compatibility for existing pricing/stock logic.
                'base_price_net' => $price,
                'stock_qty' => $stockItem->quantity,
                'is_active' => true,
            ]
        );

        $product->categories()->syncWithoutDetaching([$category->id]);

        return $product;
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
}
