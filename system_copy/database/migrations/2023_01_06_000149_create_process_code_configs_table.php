<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for process_code_configs table
 * 
 * Combined from these migrations:
 * - 2024_03_20_000003_create_process_code_configs_table.php
 * - 2024_03_20_000006_add_approver_fields_to_process_code_configs.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('process_code_configs', function (Blueprint $table) {
            $table->id();
            $table->string('process_code');
            $table->string('process_name');
            $table->text('description');
            $table->boolean('requires_first_checker')->default(true);
            $table->boolean('requires_second_checker')->default(true);
            $table->json('first_checker_roles')->nullable();
            $table->json('second_checker_roles')->nullable();
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->boolean('requires_approver')->default(true);
            $table->json('approver_roles')->nullable();
            $table->timestamps();
            $table->index(['process_code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('process_code_configs');
    }
};