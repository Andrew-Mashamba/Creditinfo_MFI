<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for cash_movements table
 * 
 * Combined from these migrations:
 * - 2025_01_16_120003_add_vault_id_to_cash_movements_table.php
 * - 2025_07_07_074638_create_cash_movements_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('cash_movements', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('vault_id')->nullable();
            $table->bigInteger('strongroom_ledger_id')->nullable();
            $table->string('type');
            $table->decimal('amount', 15, 2);
            $table->string('status')->default('pending');
            $table->text('description')->nullable();
            $table->bigInteger('user_id');
            $table->bigInteger('branch_id')->nullable();
            $table->string('reference', 100)->nullable();
            $table->bigInteger('from_till_id')->nullable();
            $table->bigInteger('to_till_id')->nullable();
            $table->bigInteger('initiated_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->json('denomination_breakdown')->nullable();
            $table->text('reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->text('notes')->nullable();
            $table->index(['vault_id']);
            $table->index(['vault_id', 'type', 'status']);
            $table->index(['strongroom_ledger_id']);
            $table->index(['reference']);
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('branch_id')->references('id')->on('branches');
            $table->foreign('vault_id')->references('id')->on('vaults');
            $table->foreign('strongroom_ledger_id')->references('id')->on('strongroom_ledgers');
            $table->foreign('from_till_id')->references('id')->on('tills');
            $table->foreign('to_till_id')->references('id')->on('tills');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('approved_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('cash_movements');
    }
};