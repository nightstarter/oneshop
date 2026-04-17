<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('payment_transactions', function (Blueprint $table) {
            $table->id();
            $table->foreignId('order_id')->constrained()->cascadeOnDelete();
            $table->foreignId('payment_method_id')->nullable()->constrained()->nullOnDelete();
            $table->string('provider_code', 64);
            $table->string('type', 32)->default('offline');
            $table->string('status', 32)->default('pending');
            $table->string('external_id', 128)->nullable();
            $table->string('redirect_url', 512)->nullable();
            $table->string('currency', 3)->default(config('shop.currency'));
            $table->decimal('amount_gross', 12, 2)->default(0);
            $table->json('request_payload_json')->nullable();
            $table->json('response_payload_json')->nullable();
            $table->timestamp('paid_at')->nullable();
            $table->timestamps();

            $table->index(['provider_code', 'status']);
            $table->index('external_id');
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('payment_transactions');
    }
};
