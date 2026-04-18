<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropPrimary(['product_id', 'media_file_id']);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->id();
            $table->timestamps();
            $table->unique(['product_id', 'media_file_id']);
        });
    }

    public function down(): void
    {
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropUnique('product_images_product_id_media_file_id_unique');
            $table->dropColumn(['id', 'created_at', 'updated_at']);
        });

        Schema::table('product_images', function (Blueprint $table) {
            $table->primary(['product_id', 'media_file_id']);
        });
    }
};
