<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('number')->unique();
            $table->foreignId('customer_id')->nullable()->constrained()->nullOnDelete();
            $table->foreignId('user_id')->nullable()->constrained()->nullOnDelete();
            $table->string('status')->default('draft');
            $table->string('currency', 3)->default(config('shop.currency'));
            $table->decimal('vat_rate', 5, 2);
            $table->decimal('price_net', 12, 2)->default(0);
            $table->decimal('price_vat', 12, 2)->default(0);
            $table->decimal('price_gross', 12, 2)->default(0);
            $table->json('billing_address_json')->nullable();
            $table->json('shipping_address_json')->nullable();
            $table->text('note')->nullable();
            $table->timestamp('placed_at')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('orders');
    }
};