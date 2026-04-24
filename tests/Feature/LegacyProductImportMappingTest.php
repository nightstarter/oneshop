<?php

namespace Tests\Feature;

use App\Models\Product;
use App\Models\ProductType;
use App\Models\PriceList;
use App\Models\Warehouse;
use App\Services\Import\ImportReport;
use App\Services\Import\LegacyProductImportService;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LegacyProductImportMappingTest extends TestCase
{
    use RefreshDatabase;

    public function test_legacy_import_maps_original_csv_headers_to_final_product_schema(): void
    {
        ProductType::query()->create([
            'code' => 'battery',
            'name' => 'Battery',
            'is_active' => true,
        ]);

        PriceList::query()->create([
            'name' => 'Default',
            'code' => 'DEFAULT',
            'currency' => 'CZK',
            'is_active' => true,
        ]);

        Warehouse::query()->create([
            'code' => 'MAIN',
            'name' => 'Main',
            'is_active' => true,
            'priority' => 1,
        ]);

        $service = new LegacyProductImportService(new ImportReport());

        $service->importCarrierRow([
            'ItemCode' => 'LEGACY-CSV-001',
            'ItemName' => 'Legacy CSV Product',
            'Closed' => '0',
            'Cena1' => '121.00',
            'Dispo' => '5',
            'InfoText' => 'Imported from legacy headers',
            'Typ' => 'Li-Ion',
        ]);

        $product = Product::query()->where('sku', 'LEGACY-CSV-001')->firstOrFail();

        $this->assertSame('Legacy CSV Product', $product->name);
        $this->assertTrue($product->active);
        $this->assertSame(100.0, (float) $product->price);
        $this->assertNull($product->getAttributes()['base_price_net'] ?? null);
    }
}
