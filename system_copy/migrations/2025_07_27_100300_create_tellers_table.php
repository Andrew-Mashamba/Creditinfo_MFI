<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for tellers table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_tellers_table.php
 * - 2025_07_07_074411_create_tellers_table.php
 * - 2025_07_07_102228_add_user_id_to_tellers_table.php
 * - 2025_07_07_102725_add_missing_columns_to_tellers_table.php
 * - 2025_07_07_123039_add_till_id_to_tellers_table.php
 * - 2025_07_07_124336_change_employee_id_to_string_in_tellers_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tellers', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 50)->nullable();
            $table->string('status', 10);
            $table->bigInteger('branch_id');
            $table->string('max_amount'); // Original type: double precision
            $table->bigInteger('account_id')->nullable();
            $table->bigInteger('registered_by_id');
            $table->string('progress_status', 10)->nullable();
            $table->string('teller_name', 30)->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('till_id')->nullable();
            $table->decimal('transaction_limit', 15, 2)->default(100000.00);
            $table->json('permissions')->nullable();
            $table->timestamp('last_login_at')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->bigInteger('assigned_by')->nullable();
            $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('till_id')->references('id')->on('tills');
            $table->foreign('assigned_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tellers');
    }
};