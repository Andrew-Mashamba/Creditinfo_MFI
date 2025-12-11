<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for accounts table
 * 
 * Combined from these migrations:
 * - 2024_03_13_000002_create_accounts_table.php
 * - 2024_03_14_000000_add_soft_delete_columns_to_accounts_table.php
 * - 2025_03_21_add_indexes_to_shares_tables.php
 * - 2025_06_03_023609_add_institution_id_to_accounts_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('accounts', function (Blueprint $table) {
            $table->id();
            $table->string('institution_number', 120)->nullable();
            $table->string('branch_number', 120)->nullable();
            $table->string('client_number', 120)->nullable();
            $table->string('account_use', 120)->nullable();
            $table->string('product_number', 120)->nullable();
            $table->string('sub_product_number', 120)->nullable();
            $table->string('major_category_code', 20)->nullable();
            $table->string('category_code', 20)->nullable();
            $table->string('sub_category_code', 20)->nullable();
            $table->string('member_account_code', 20)->nullable();
            $table->string('account_name', 200)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->string('status', 100)->default('PENDING');
            $table->string('balance')->default('0'); // Original type: double precision
            $table->text('notes')->nullable();
            $table->string('mirror_account')->nullable();
            $table->string('employeeId')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->decimal('locked_amount', 15, 2)->nullable();
            $table->string('suspense_account')->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('account_level', 50)->nullable();
            $table->string('credit', 150)->nullable();
            $table->string('debit', 150)->nullable();
            $table->string('type')->nullable();
            $table->string('parent_account_number', 150)->nullable();
            $table->integer('percent')->nullable();
            $table->softDeletes();
            $table->string('deleted_by')->nullable();
            $table->bigInteger('institution_id')->nullable();
            $table->timestamps();
            $table->index(['account_number']);
            $table->index(['client_number']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['client_number', 'type']);
            $table->index(['account_number', 'status']);
            $table->foreign('institution_id')->references('id')->on('institutions')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('accounts');
    }
};