<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Schema;
use Tests\TestCase;

class ProductSchemaBackfillTest extends TestCase
{
    use RefreshDatabase;

    public function test_cleanup_migration_removed_legacy_product_columns(): void
    {
        $this->assertFalse(Schema::hasColumn('products', 'base_price_net'));
        $this->assertFalse(Schema::hasColumn('products', 'stock_qty'));
        $this->assertFalse(Schema::hasColumn('products', 'is_active'));
    }
}
