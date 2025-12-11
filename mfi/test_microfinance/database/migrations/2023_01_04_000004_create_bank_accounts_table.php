<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for bank_accounts table
 * 
 * Combined from these migrations:
 * - 2024_03_19_000000_create_bank_accounts_table.php
 * - 2025_07_09_154942_add_account_type_to_bank_accounts_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bank_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('bank_name')->nullable();
            $table->string('account_name')->nullable();
            $table->string('account_number')->nullable();
            $table->string('branch_name')->nullable();
            $table->string('swift_code')->nullable();
            $table->string('currency')->default('TZS');
            $table->decimal('opening_balance', 15, 2)->nullable()->default('0');
            $table->decimal('current_balance', 15, 2)->nullable()->default('0');
            $table->string('internal_mirror_account_number')->nullable();
            $table->string('status')->default('ACTIVE');
            $table->text('description')->nullable();
            $table->string('created_by')->nullable();
            $table->string('updated_by')->nullable();
            $table->softDeletes();
            $table->string('account_type')->default('main_operations');
            $table->bigInteger('branch_id')->nullable();
            $table->timestamps();
            $table->index(['bank_name']);
            $table->index(['account_number']);
            $table->index(['status']);
            $table->index(['account_type', 'branch_id']);
            $table->foreign('branch_id')->references('id')->on('branches');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_accounts');
    }
};