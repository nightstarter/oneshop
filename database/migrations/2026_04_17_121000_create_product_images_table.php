<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_images', function (Blueprint $table) {
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('media_file_id')->constrained()->cascadeOnDelete();
            $table->unsignedInteger('sort_order')->default(0);
            $table->string('alt')->nullable();
            $table->boolean('is_primary')->default(false);

            $table->primary(['product_id', 'media_file_id']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_images');
    }
};