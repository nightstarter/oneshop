<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('stock_item_id')->nullable()->after('id')->constrained('stock_items')->nullOnDelete();
            $table->decimal('price', 12, 2)->nullable()->after('description');
            $table->boolean('active')->default(true)->after('price')->index();
            $table->string('visibility', 32)->default('public')->after('active')->index();
            $table->index('stock_item_id');
        });

        DB::table('products')->update([
            'price' => DB::raw('base_price_net'),
            'active' => DB::raw('is_active'),
        ]);
    }

    public function down(): void
    {
        Schema::table('products', function (Blueprint $table) {
            $table->dropIndex(['stock_item_id']);
            $table->dropConstrainedForeignId('stock_item_id');
            $table->dropIndex(['active']);
            $table->dropIndex(['visibility']);
            $table->dropColumn(['price', 'active', 'visibility']);
        });
    }
};
