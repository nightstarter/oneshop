<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        if (Schema::hasTable('compatibility_models')) {
            return;
        }

        Schema::create('compatibility_models', function (Blueprint $table) {
            $table->id();
            $table->string('brand', 128)->index();
            $table->string('model_name', 191);
            $table->string('model_code', 128)->nullable()->index();
            $table->string('slug')->unique();
            $table->boolean('active')->default(true)->index();
            $table->timestamps();

            $table->unique(['brand', 'model_name']);
        });
    }

    public function down(): void
    {
        if (!Schema::hasTable('compatibility_models')) {
            return;
        }

        Schema::dropIfExists('compatibility_models');
    }
};
