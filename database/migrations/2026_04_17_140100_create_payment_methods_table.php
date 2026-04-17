<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_methods', function (Blueprint $table) {
            $table->id();
            $table->string('code')->unique();
            $table->string('name');
            $table->string('provider_code');
            $table->string('type')->default('offline');
            $table->boolean('is_active')->default(true);
            $table->decimal('price_net', 12, 2)->default(0);
            $table->decimal('price_gross', 12, 2)->default(0);
            $table->unsignedInteger('sort_order')->default(0);
            $table->json('payload_json')->nullable();
            $table->timestamps();

            $table->index(['is_active', 'sort_order']);
            $table->index('provider_code');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_methods');
    }
};
