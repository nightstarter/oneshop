<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (Schema::hasColumn('products', 'base_price_net')) {
                $table->dropColumn('base_price_net');
            }

            if (Schema::hasColumn('products', 'stock_qty')) {
                $table->dropColumn('stock_qty');
            }

            if (Schema::hasColumn('products', 'is_active')) {
                $table->dropColumn('is_active');
            }
        });
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            if (! Schema::hasColumn('products', 'base_price_net')) {
                $table->decimal('base_price_net', 12, 2)->nullable()->after('description');
            }

            if (! Schema::hasColumn('products', 'stock_qty')) {
                $table->integer('stock_qty')->default(0)->after('base_price_net');
            }

            if (! Schema::hasColumn('products', 'is_active')) {
                $table->boolean('is_active')->default(true)->after('stock_qty');
            }
        });
    }
};
