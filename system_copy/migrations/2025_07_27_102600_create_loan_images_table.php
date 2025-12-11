<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for loan_images table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_loan_images_table.php
 * - 2025_06_24_213522_add_missing_fields_to_loan_images_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('loan_images', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id')->nullable();
            $table->string('category')->nullable();
            $table->string('filename')->nullable();
            $table->text('url')->nullable();
            $table->text('document_descriptions')->nullable();
            $table->string('document_category')->nullable();
            $table->bigInteger('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->string('original_name')->nullable();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_images');
    }
};