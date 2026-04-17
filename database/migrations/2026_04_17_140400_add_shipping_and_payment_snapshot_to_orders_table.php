<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->foreignId('shipping_method_id')->nullable()->after('user_id')->constrained()->nullOnDelete();
            $table->string('shipping_code')->nullable()->after('shipping_method_id');
            $table->string('shipping_name')->nullable()->after('shipping_code');
            $table->decimal('shipping_price_net', 12, 2)->default(0)->after('shipping_name');
            $table->decimal('shipping_price_gross', 12, 2)->default(0)->after('shipping_price_net');

            $table->foreignId('payment_method_id')->nullable()->after('shipping_price_gross')->constrained()->nullOnDelete();
            $table->string('payment_code')->nullable()->after('payment_method_id');
            $table->string('payment_name')->nullable()->after('payment_code');
            $table->decimal('payment_price_net', 12, 2)->default(0)->after('payment_name');
            $table->decimal('payment_price_gross', 12, 2)->default(0)->after('payment_price_net');

            $table->string('pickup_point_id')->nullable()->after('payment_price_gross');
            $table->string('pickup_point_name')->nullable()->after('pickup_point_id');
            $table->string('pickup_point_address')->nullable()->after('pickup_point_name');

            $table->json('shipping_payload_json')->nullable()->after('pickup_point_address');
            $table->json('payment_payload_json')->nullable()->after('shipping_payload_json');
        });
    }

    public function down(): void
    {
        Schema::table('orders', function (Blueprint $table) {
            $table->dropConstrainedForeignId('shipping_method_id');
            $table->dropConstrainedForeignId('payment_method_id');

            $table->dropColumn([
                'shipping_code',
                'shipping_name',
                'shipping_price_net',
                'shipping_price_gross',
                'payment_code',
                'payment_name',
                'payment_price_net',
                'payment_price_gross',
                'pickup_point_id',
                'pickup_point_name',
                'pickup_point_address',
                'shipping_payload_json',
                'payment_payload_json',
            ]);
        });
    }
};
