<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Catalog compatibility extension.
 *
 * New tables:
 *   device_models             – list of device models (notebooks, etc.)
 *   part_numbers              – typová označení baterie / nabíječky
 *   catalog_product_device_models – N:M carrier product <-> device_model
 *   catalog_product_part_numbers  – N:M carrier product <-> part_number
 *
 * Products table additions:
 *   parent_product_id     – NULL = nosný produkt; FK = SEO produkt (dědí data z nosiče)
 *   linked_device_model_id – optional: SEO produkt navázaný na konkrétní device_model
 */
return new class extends Migration
{
    public function up(): void
    {
        // 1. device_models ─────────────────────────────────────────────────
        if (! Schema::hasTable('device_models')) {
            Schema::create('device_models', function (Blueprint $table) {
                $table->id();
                $table->string('brand', 96)->nullable()->index();
                $table->string('model_name', 128);
                // Pre-computed normalised value (lowercase, dots/dashes removed)
                // used for fast LIKE searches without function indexes.
                $table->string('model_normalized', 128)->index();
                $table->string('slug')->unique();
                // Legacy import columns
                $table->string('legacy_ex_id', 64)->nullable()->index();
                $table->string('legacy_art_id', 64)->nullable()->index();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();

                $table->unique(['brand', 'model_name']);
            });
        }

        // 2. part_numbers ──────────────────────────────────────────────────
        if (! Schema::hasTable('part_numbers')) {
            Schema::create('part_numbers', function (Blueprint $table) {
                $table->id();
                // The raw part number / type code, e.g. "PA3817U-1BRS"
                $table->string('value', 128)->unique();
                $table->string('value_normalized', 128)->index();
                // Legacy import columns
                $table->string('legacy_ex_id', 64)->nullable()->index();
                $table->string('legacy_art_id', 64)->nullable()->index();
                $table->boolean('active')->default(true)->index();
                $table->timestamps();
            });
        }

        // 3. Extend products table ─────────────────────────────────────────
        if (! Schema::hasColumn('products', 'parent_product_id')) {
            Schema::table('products', function (Blueprint $table) {
                // SEO product concept: parent_product_id set → this is an SEO product
                $table->unsignedBigInteger('parent_product_id')
                    ->nullable()->after('id')->index();
            });
        }

        if (! Schema::hasColumn('products', 'linked_device_model_id')) {
            Schema::table('products', function (Blueprint $table) {
                // Optional: SEO product pinned to one specific device model
                $table->unsignedBigInteger('linked_device_model_id')
                    ->nullable()->after('parent_product_id')->index();
            });
        }

        // FK for linked_device_model_id (device_models now exists)
        if (
            Schema::hasColumn('products', 'parent_product_id')
            && Schema::hasTable('products')
            && ! $this->hasForeignKey('products', 'products_parent_product_id_foreign')
        ) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('parent_product_id')
                    ->references('id')->on('products')->nullOnDelete();
            });
        }

        if (
            Schema::hasColumn('products', 'linked_device_model_id')
            && Schema::hasTable('device_models')
            && ! $this->hasForeignKey('products', 'products_linked_device_model_id_foreign')
        ) {
            Schema::table('products', function (Blueprint $table) {
                $table->foreign('linked_device_model_id')
                    ->references('id')->on('device_models')->nullOnDelete();
            });
        }

        // 4. catalog_product_device_models ────────────────────────────────
        // Vazba je vždy na nosný (carrier) produkt, nikdy ne na SEO produkt.
        if (! Schema::hasTable('catalog_product_device_models')) {
            Schema::create('catalog_product_device_models', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('device_model_id')->constrained('device_models')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'device_model_id']);
                $table->index('device_model_id');
            });
        }

        // 5. catalog_product_part_numbers ─────────────────────────────────
        if (! Schema::hasTable('catalog_product_part_numbers')) {
            Schema::create('catalog_product_part_numbers', function (Blueprint $table) {
                $table->id();
                $table->foreignId('product_id')->constrained()->cascadeOnDelete();
                $table->foreignId('part_number_id')->constrained('part_numbers')->cascadeOnDelete();
                $table->timestamps();

                $table->unique(['product_id', 'part_number_id']);
                $table->index('part_number_id');
            });
        }
    }

    public function down(): void
    {
        Schema::dropIfExists('catalog_product_part_numbers');
        Schema::dropIfExists('catalog_product_device_models');

        if (Schema::hasTable('products')) {
            Schema::table('products', function (Blueprint $table) {
                if ($this->hasForeignKey('products', 'products_linked_device_model_id_foreign')) {
                    $table->dropForeign(['linked_device_model_id']);
                }

                if ($this->hasForeignKey('products', 'products_parent_product_id_foreign')) {
                    $table->dropForeign(['parent_product_id']);
                }

                if (Schema::hasColumn('products', 'linked_device_model_id')) {
                    $table->dropColumn('linked_device_model_id');
                }

                if (Schema::hasColumn('products', 'parent_product_id')) {
                    $table->dropColumn('parent_product_id');
                }
            });
        }

        Schema::dropIfExists('part_numbers');
        Schema::dropIfExists('device_models');
    }

    private function hasForeignKey(string $table, string $constraint): bool
    {
        $result = DB::selectOne(
            'SELECT COUNT(*) AS aggregate
             FROM information_schema.KEY_COLUMN_USAGE
             WHERE TABLE_SCHEMA = DATABASE()
               AND TABLE_NAME = ?
               AND CONSTRAINT_NAME = ?
               AND REFERENCED_TABLE_NAME IS NOT NULL',
            [$table, $constraint]
        );

        return ((int) ($result->aggregate ?? 0)) > 0;
    }
};
