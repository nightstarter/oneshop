<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('addresses', function (Blueprint $table) {
            $table->id();
            $table->foreignId('customer_id')->constrained()->cascadeOnDelete();
            $table->string('type')->default('billing');
            $table->string('company_name')->nullable();
            $table->string('first_name');
            $table->string('last_name');
            $table->string('street');
            $table->string('city');
            $table->string('zip', 20);
            $table->string('country_code', 2)->default('CZ');
            $table->string('phone')->nullable();
            $table->boolean('is_default')->default(false);
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('addresses');
    }
};