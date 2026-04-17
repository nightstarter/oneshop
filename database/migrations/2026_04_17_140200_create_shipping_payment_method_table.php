<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('shipping_payment_method', function (Blueprint $table) {
            $table->id();
            $table->foreignId('shipping_method_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->constrained()->cascadeOnDelete();
            $table->timestamps();

            $table->unique(['shipping_method_id', 'payment_method_id'], 'ship_pay_unique');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('shipping_payment_method');
    }
};
