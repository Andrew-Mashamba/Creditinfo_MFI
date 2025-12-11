<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for vaults table
 * 
 * Combined from these migrations:
 * - 2025_01_16_120000_create_vaults_table.php
 * - 2025_07_09_142445_add_parent_account_to_vaults_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('vaults', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 100);
            $table->bigInteger('branch_id');
            $table->decimal('current_balance', 15, 2)->default('0');
            $table->decimal('limit', 15, 2);
            $table->integer('warning_threshold')->default(80);
            $table->string('bank_name')->nullable();
            $table->string('bank_account_number')->nullable();
            $table->string('internal_account_number')->nullable();
            $table->boolean('auto_bank_transfer')->default(false);
            $table->boolean('requires_dual_approval')->default(false);
            $table->boolean('send_alerts')->default(true);
            $table->string('status')->default('active');
            $table->text('description')->nullable();
            $table->string('parent_account')->nullable();
            $table->timestamps();
            $table->index(['branch_id']);
            $table->index(['status']);
            $table->index(['current_balance']);
            $table->index(['code']);
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('vaults');
    }
};