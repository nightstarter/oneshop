<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('product_types', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('is_active')->default(true)->index();
            $table->timestamps();
        });

        Schema::table('products', function (Blueprint $table) {
            $table->foreignId('product_type_id')->nullable()->after('stock_item_id')->constrained('product_types')->nullOnDelete();
            $table->string('legacy_item_code', 64)->nullable()->after('sku')->index();
            $table->string('legacy_group_id', 64)->nullable()->after('legacy_item_code')->index();
            $table->unsignedBigInteger('legacy_sphinx_id')->nullable()->after('legacy_group_id')->index();
            $table->json('legacy_payload')->nullable()->after('description');
        });

        Schema::create('attributes', function (Blueprint $table) {
            $table->id();
            $table->string('code', 64)->unique();
            $table->string('name');
            $table->string('data_type', 32)->default('text')->index();
            $table->string('unit', 32)->nullable();
            $table->boolean('is_filterable')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();
        });

        Schema::create('attribute_product_type', function (Blueprint $table) {
            $table->id();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->foreignId('product_type_id')->constrained('product_types')->cascadeOnDelete();
            $table->boolean('is_required')->default(false);
            $table->boolean('is_filterable')->default(false);
            $table->unsignedInteger('sort_order')->default(0);
            $table->timestamps();

            $table->unique(['attribute_id', 'product_type_id']);
        });

        Schema::create('product_attribute_values', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('attribute_id')->constrained('attributes')->cascadeOnDelete();
            $table->text('value_text')->nullable();
            $table->decimal('value_number', 14, 4)->nullable();
            $table->boolean('value_boolean')->nullable();
            $table->json('value_json')->nullable();
            $table->string('value_unit', 32)->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'attribute_id']);
            $table->index(['attribute_id', 'value_number']);
            $table->index(['attribute_id', 'value_boolean']);
        });

        Schema::create('product_prices', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('price_list_id')->constrained('price_lists')->cascadeOnDelete();
            $table->decimal('price_net', 12, 2);
            $table->decimal('price_gross', 12, 2)->nullable();
            $table->dateTime('valid_from')->nullable();
            $table->dateTime('valid_to')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'price_list_id', 'valid_from'], 'product_prices_product_price_list_valid_from_unique');
            $table->index(['price_list_id', 'valid_from', 'valid_to']);
        });

        Schema::create('warehouses', function (Blueprint $table) {
            $table->id();
            $table->string('code', 32)->unique();
            $table->string('name');
            $table->boolean('is_sale')->default(false)->index();
            $table->boolean('is_active')->default(true)->index();
            $table->unsignedInteger('priority')->default(0)->index();
            $table->timestamps();
        });

        Schema::create('product_stocks', function (Blueprint $table) {
            $table->id();
            $table->foreignId('product_id')->constrained()->cascadeOnDelete();
            $table->foreignId('warehouse_id')->constrained('warehouses')->cascadeOnDelete();
            $table->integer('quantity_on_hand')->default(0);
            $table->integer('quantity_reserved')->default(0);
            $table->integer('quantity_incoming')->default(0);
            $table->boolean('backorderable')->default(false);
            $table->dateTime('available_from')->nullable();
            $table->timestamps();

            $table->unique(['product_id', 'warehouse_id']);
            $table->index(['warehouse_id', 'quantity_on_hand']);
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('product_stocks');
        Schema::dropIfExists('warehouses');
        Schema::dropIfExists('product_prices');
        Schema::dropIfExists('product_attribute_values');
        Schema::dropIfExists('attribute_product_type');
        Schema::dropIfExists('attributes');

        Schema::table('products', function (Blueprint $table) {
            $table->dropConstrainedForeignId('product_type_id');
            $table->dropIndex(['legacy_item_code']);
            $table->dropIndex(['legacy_group_id']);
            $table->dropIndex(['legacy_sphinx_id']);
            $table->dropColumn(['legacy_item_code', 'legacy_group_id', 'legacy_sphinx_id', 'legacy_payload']);
        });

        Schema::dropIfExists('product_types');
    }
};
