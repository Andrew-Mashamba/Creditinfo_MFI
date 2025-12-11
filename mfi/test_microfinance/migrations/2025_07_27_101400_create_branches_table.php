<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for branches table
 * 
 * Combined from these migrations:
 * - 2024_03_12_000000_create_branches_table.php
 * - 2025_07_09_175658_add_cit_provider_id_to_branches_table.php
 * - 2025_07_09_195204_add_missing_fields_to_branches_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('branches', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('region');
            $table->string('wilaya');
            $table->string('branch_number');
            $table->string('status')->default('PENDING');
            $table->string('email')->nullable();
            $table->string('phone_number')->nullable();
            $table->text('address')->nullable();
            $table->string('branch_type')->default('SUB');
            $table->date('opening_date')->nullable();
            $table->string('branch_manager')->nullable();
            $table->string('operating_hours')->nullable();
            $table->json('services_offered')->nullable();
            $table->bigInteger('cit_provider_id')->nullable();
            $table->string('vault_account', 50)->nullable();
            $table->string('till_account', 50)->nullable();
            $table->string('petty_cash_account', 50)->nullable();
            $table->index(['branch_number']);
            $table->index(['cit_provider_id']);
            $table->foreign('cit_provider_id')->references('id')->on('cash_in_transit_providers');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('branches');
    }
};