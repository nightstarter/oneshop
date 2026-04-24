<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up(): void
    {
        DB::statement('ALTER TABLE products MODIFY base_price_net DECIMAL(12,2) NULL');
    }

    public function down(): void
    {
        DB::statement('UPDATE products SET base_price_net = COALESCE(base_price_net, price, 0)');
        DB::statement('ALTER TABLE products MODIFY base_price_net DECIMAL(12,2) NOT NULL');
    }
};
