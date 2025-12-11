<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for employees table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_employees_table.php
 * - 2024_03_21_000001_add_employee_details_columns.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('employees', function (Blueprint $table) {
            $table->id();
            $table->string('institution_user_id', 50)->nullable();
            $table->string('first_name', 50)->nullable();
            $table->string('middle_name', 50)->nullable();
            $table->string('last_name', 50)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('gender')->nullable();
            $table->string('marital_status')->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('address', 100)->nullable();
            $table->string('street')->nullable();
            $table->string('city', 100)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('ward', 100)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('job_title', 100)->nullable();
            $table->integer('branch_id')->nullable();
            $table->date('hire_date')->nullable();
            $table->decimal('basic_salary', 10, 2)->nullable();
            $table->decimal('gross_salary', 10, 2)->nullable();
            $table->string('payment_frequency', 50)->nullable();
            $table->string('employee_status')->nullable();
            $table->integer('registering_officer')->nullable();
            $table->string('employment_type', 100)->nullable();
            $table->string('emergency_contact_name')->nullable();
            $table->string('emergency_contact_relationship', 100)->nullable();
            $table->string('emergency_contact_phone', 20)->nullable();
            $table->string('emergency_contact_email', 100)->nullable();
            $table->integer('department_id')->nullable();
            $table->integer('role_id')->nullable();
            $table->integer('reporting_manager_id')->nullable();
            $table->string('employee_number', 100)->nullable();
            $table->string('notes')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->string('next_of_kin_name')->nullable();
            $table->string('place_of_birth')->nullable();
            $table->string('next_of_kin_phone', 20)->nullable();
            $table->string('tin_number', 30)->nullable();
            $table->string('nida_number', 30)->nullable();
            $table->string('nssf_number', 30)->nullable();
            $table->decimal('nssf_rate', 5, 2)->nullable();
            $table->string('nhif_number', 30)->nullable();
            $table->decimal('nhif_rate', 5, 2)->nullable();
            $table->decimal('workers_compensation', 10, 2)->nullable();
            $table->decimal('life_insurance', 10, 2)->nullable();
            $table->string('tax_category', 50)->nullable();
            $table->decimal('paye_rate', 5, 2)->nullable();
            $table->decimal('tax_paid', 10, 2)->nullable();
            $table->decimal('pension', 10, 2)->nullable();
            $table->decimal('nhif', 10, 2)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->string('approval_stage')->nullable();
            $table->integer('user_id')->nullable();
            $table->integer('client_id')->nullable();
            $table->string('physical_address')->nullable();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('employees');
    }
};