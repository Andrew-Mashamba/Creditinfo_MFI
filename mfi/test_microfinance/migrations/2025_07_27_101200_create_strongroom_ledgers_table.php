<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for strongroom_ledgers table
 * 
 * Combined from these migrations:
 * - 2025_01_16_120002_add_vault_id_to_strongroom_ledgers_table.php
 * - 2025_07_07_074121_create_strongroom_ledgers_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('strongroom_ledgers', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vault_id')->nullable();
            $table->decimal('balance', 15, 2)->default('0');
            $table->decimal('total_deposits', 15, 2)->default('0');
            $table->decimal('total_withdrawals', 15, 2)->default('0');
            $table->json('denomination_breakdown')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->string('vault_code', 50);
            $table->string('status')->default('active');
            $table->text('notes')->nullable();
            $table->timestamp('last_transaction_at')->nullable();
            $table->index(['status']);
            $table->index(['vault_id']);
            $table->index(['vault_code']);
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('vault_id')->references('id')->on('vaults');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('strongroom_ledgers');
    }
};