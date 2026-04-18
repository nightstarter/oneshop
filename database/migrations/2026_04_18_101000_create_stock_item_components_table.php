<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('stock_item_components')) {
            return;
        }

        Schema::create('stock_item_components', function (Blueprint $table) {
            $table->id();
            $table->foreignId('stock_item_id')->constrained('stock_items')->cascadeOnDelete();
            $table->foreignId('component_stock_item_id')->constrained('stock_items')->cascadeOnDelete();
            $table->unsignedInteger('quantity')->default(1);
            $table->timestamps();

            $table->unique(['stock_item_id', 'component_stock_item_id'], 'stock_item_components_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('stock_item_components')) {
            return;
        }

        Schema::dropIfExists('stock_item_components');
    }
};
