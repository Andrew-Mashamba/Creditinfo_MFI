<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for approvals table
 * 
 * Combined from these migrations:
 * - 2024_03_20_000007_recreate_approvals_table_with_all_fields.php
 * - 2025_06_03_040000_recreate_approvals_table_with_nullable_institution.php
 * - 2025_06_09_044913_add_checker_level_to_approvals_table.php
 * - 2025_06_11_064032_fix_approvals_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approvals', function (Blueprint $table) {
            $table->id();
            $table->string('status')->default('pending');
            $table->softDeletes();
            $table->integer('checker_level')->nullable();
            $table->bigInteger('first_checker_id')->nullable();
            $table->bigInteger('second_checker_id')->nullable();
            $table->string('first_checker_status')->nullable();
            $table->string('second_checker_status')->nullable();
            $table->string('rejection_reason')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamp('rejected_at')->nullable();
            $table->timestamp('first_checked_at')->nullable();
            $table->timestamp('second_checked_at')->nullable();
            $table->text('comments')->nullable();
            $table->bigInteger('last_action_by')->nullable();
            $table->string('process_name')->nullable();
            $table->string('process_description')->nullable();
            $table->string('approval_process_description')->nullable();
            $table->string('process_code')->nullable();
            $table->bigInteger('process_id')->nullable();
            $table->string('process_status')->nullable();
            $table->string('approval_status')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->bigInteger('approver_id')->nullable();
            $table->string('team_id')->nullable();
            $table->json('edit_package')->nullable();
            $table->string('first_checker_rejection_reason')->nullable();
            $table->string('second_checker_rejection_reason')->nullable();
            $table->string('approver_rejection_reason')->nullable();
            $table->foreign('first_checker_id')->references('id')->on('users');
            $table->foreign('second_checker_id')->references('id')->on('users');
            $table->foreign('last_action_by')->references('id')->on('users');
            $table->foreign('user_id')->references('id')->on('users');
            $table->foreign('approver_id')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approvals');
    }
};