<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for users table
 * 
 * Combined from these migrations:
 * - 2014_10_12_000000_create_users_table.php
 * - 2014_10_12_200000_add_two_factor_columns_to_users_table.php
 * - 2024_03_19_000003_update_users_table_department_to_department_code.php
 * - 2024_03_21_000000_add_password_changed_at_to_users_table.php
 * - 2025_05_20_154826_add_otp_fields_to_users_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('users', function (Blueprint $table) {
            $table->id();
            $table->string('institution_user_id')->nullable();
            $table->string('name')->nullable();
            $table->string('email')->nullable();
            $table->timestamp('email_verified_at')->nullable();
            $table->string('password')->nullable();
            $table->rememberToken();
            $table->integer('current_team_id')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('role')->nullable();
            $table->string('status')->nullable();
            $table->time('otp_time')->nullable();
            $table->integer('otp')->nullable();
            $table->string('verification_status')->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('employeeId')->nullable();
            $table->string('department_code', 10)->nullable();
            $table->string('sub_role')->nullable();
            $table->string('branch', 30)->nullable();
            $table->timestamp('last_update_password')->useCurrent();
            $table->string('token')->nullable();
            $table->time('token_expires_at')->nullable();
            $table->text('two_factor_secret')->nullable();
            $table->text('two_factor_recovery_codes')->nullable();
            $table->timestamp('two_factor_confirmed_at')->nullable();
            $table->timestamp('password_changed_at')->nullable();
            $table->string('otp_hash')->nullable();
            $table->timestamp('otp_expires_at')->nullable();
            $table->integer('otp_attempts')->default(0);
            $table->timestamp('otp_last_sent_at')->nullable();
            $table->timestamp('otp_locked_until')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('users');
    }
};