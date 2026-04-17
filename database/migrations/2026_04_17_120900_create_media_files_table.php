<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        Schema::create('media_files', function (Blueprint $table) {
            $table->id();
            $table->string('disk')->default('public');
            $table->string('path');
            $table->string('original_name');
            $table->string('mime_type');
            $table->string('extension')->nullable();
            $table->unsignedBigInteger('size');
            $table->string('checksum', 64)->unique();
            $table->unsignedInteger('width')->nullable();
            $table->unsignedInteger('height')->nullable();
            $table->timestamps();
        });
    }

    public function down(): void
    {
        Schema::dropIfExists('media_files');
    }
};