<?php

use App\Services\ProductSchemaBackfillService;
use Illuminate\Database\Migrations\Migration;

return new class extends Migration
{
    public function up(): void
    {
        app(ProductSchemaBackfillService::class)->run();
    }

    public function down(): void
    {
        // Irreversible data backfill. Rollback is handled operationally in the runbook.
    }
};
