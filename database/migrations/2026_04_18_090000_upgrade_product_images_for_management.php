<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Nejdřív zahodit foreign keys, aby se dalo zahodit primary key
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['media_file_id']);
        });

        // Teď lze zahodit primary key a přidat nový sloupeček
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropPrimary(['product_id', 'media_file_id']);
            $table->id();
            $table->timestamps();
            $table->unique(['product_id', 'media_file_id']);
        });

        // Znovu vytvořit foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Zahodit nové foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['media_file_id']);
        });

        // Zahodit nový unique constraint a sloupečky
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropUnique('product_images_product_id_media_file_id_unique');
            $table->dropColumn(['id', 'created_at', 'updated_at']);
            $table->primary(['product_id', 'media_file_id']);
        });

        // Znovu vytvořit původní foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->cascadeOnDelete();
        });
    }
};
