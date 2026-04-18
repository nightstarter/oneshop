<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Krok 1: Zahodit foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['media_file_id']);
        });

        // Krok 2: Zahodit starý primary key
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Krok 3: Přidat nový id a timestamps
        Schema::table('product_images', function (Blueprint $table) {
            $table->id()->first();
            $table->timestamps();
            $table->unique(['product_id', 'media_file_id']);
        });

        // Krok 4: Znovu vytvořit foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->cascadeOnDelete();
        });
    }

    public function down(): void
    {
        // Krok 1: Zahodit nové foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropForeign(['product_id']);
            $table->dropForeign(['media_file_id']);
        });

        // Krok 2: Zahodit nový primary key (id)
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropPrimary();
        });

        // Krok 3: Zahodit unique constraint a timestamps
        Schema::table('product_images', function (Blueprint $table) {
            $table->dropUnique('product_images_product_id_media_file_id_unique');
            $table->dropColumn(['created_at', 'updated_at']);
        });

        // Krok 4: Obnovit starý primary key
        Schema::table('product_images', function (Blueprint $table) {
            $table->primary(['product_id', 'media_file_id']);
        });

        // Krok 5: Znovu vytvořit původní foreign keys
        Schema::table('product_images', function (Blueprint $table) {
            $table->foreign('product_id')->references('id')->on('products')->cascadeOnDelete();
            $table->foreign('media_file_id')->references('id')->on('media_files')->cascadeOnDelete();
        });
    }
};
