<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for tills table
 * 
 * Combined from these migrations:
 * - 2025_07_07_071715_create_tills_table.php
 * - 2025_07_07_132414_add_assigned_to_to_tills_table.php
 * - 2025_07_09_185046_add_till_account_number_to_tills_table.php
 * - 2025_07_10_095734_add_till_management_columns_to_tills_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('tills', function (Blueprint $table) {
            $table->id();
            $table->string('name', 100);
            $table->string('till_number', 50);
            $table->bigInteger('branch_id');
            $table->decimal('current_balance', 15, 2)->default('0');
            $table->decimal('opening_balance', 15, 2)->default('0');
            $table->decimal('maximum_limit', 15, 2)->default('500000');
            $table->decimal('minimum_limit', 15, 2)->default('10000');
            $table->string('status')->default('closed');
            $table->timestamp('opened_at')->nullable();
            $table->timestamp('closed_at')->nullable();
            $table->bigInteger('opened_by')->nullable();
            $table->bigInteger('closed_by')->nullable();
            $table->bigInteger('assigned_to')->nullable();
            $table->json('denomination_breakdown')->nullable();
            $table->boolean('requires_supervisor_approval')->default(false);
            $table->text('description')->nullable();
            $table->string('till_account_number', 50)->nullable();
            $table->bigInteger('assigned_user_id')->nullable();
            $table->timestamp('assigned_at')->nullable();
            $table->text('assignment_notes')->nullable();
            $table->string('code', 20)->nullable();
            $table->decimal('variance', 15, 2)->default('0');
            $table->text('variance_explanation')->nullable();
            $table->decimal('closing_balance', 15, 2)->nullable();
            $table->timestamps();
            $table->index(['branch_id']);
            $table->index(['status']);
            $table->index(['till_number']);
            $table->index(['till_account_number']);
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('cascade');
            $table->foreign('opened_by')->references('id')->on('users');
            $table->foreign('closed_by')->references('id')->on('users');
            $table->foreign('assigned_to')->references('id')->on('users');
            $table->foreign('assigned_user_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('tills');
    }
};