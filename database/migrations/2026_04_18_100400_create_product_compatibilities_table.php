<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('product_compatibilities')) {
            return;
        }

        Schema::create('product_compatibilities', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('compatibility_model_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['product_id', 'compatibility_model_id'], 'product_compatibilities_unique');
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('product_compatibilities')) {
            return;
        }

        Schema::dropIfExists('product_compatibilities');
    }
};
