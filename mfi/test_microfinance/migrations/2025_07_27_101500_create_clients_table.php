<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for clients table
 * 
 * Combined from these migrations:
 * - 2024_03_13_000000_create_clients_table.php
 * - 2024_03_13_000001_add_unique_index_to_client_number_in_clients_table.php
 * - 2025_01_15_120000_add_portal_access_fields_to_clients_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('clients', function (Blueprint $table) {
            $table->id();
            $table->string('account_number', 50);
            $table->string('first_name', 100)->nullable();
            $table->string('middle_name', 100)->nullable();
            $table->string('last_name', 100)->nullable();
            $table->string('branch', 100)->nullable();
            $table->string('registering_officer', 100)->nullable();
            $table->string('loan_officer', 100)->nullable();
            $table->string('approving_officer', 100)->nullable();
            $table->string('membership_type', 50)->nullable();
            $table->string('incorporation_number', 50)->nullable();
            $table->string('phone_number', 20)->nullable();
            $table->string('mobile_phone_number', 20)->nullable();
            $table->string('email', 100)->nullable();
            $table->string('place_of_birth', 100)->nullable();
            $table->string('marital_status', 20)->nullable();
            $table->string('client_number', 50)->nullable();
            $table->date('registration_date')->nullable();
            $table->string('address')->nullable();
            $table->string('notes')->nullable();
            $table->bigInteger('current_team_id')->nullable();
            $table->string('profile_photo_path')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->string('client_status', 50)->nullable();
            $table->string('next_of_kin_name', 100)->nullable();
            $table->string('next_of_kin_phone', 20)->nullable();
            $table->string('tin_number', 50)->nullable();
            $table->string('nida_number', 50)->nullable();
            $table->string('ref_number', 50)->nullable();
            $table->string('shares_ref_number', 50)->nullable();
            $table->string('nationarity', 100)->nullable();
            $table->string('full_name')->nullable();
            $table->decimal('amount', 15, 2)->nullable();
            $table->string('national_id', 50)->nullable();
            $table->string('client_id', 50)->nullable();
            $table->string('customer_code', 50)->nullable();
            $table->string('present_surname', 100)->nullable();
            $table->string('birth_surname', 100)->nullable();
            $table->integer('number_of_spouse')->nullable();
            $table->integer('number_of_children')->nullable();
            $table->string('classification_of_individual', 50)->nullable();
            $table->string('gender', 10)->nullable();
            $table->date('date_of_birth')->nullable();
            $table->string('country_of_birth', 100)->nullable();
            $table->string('fate_status', 50)->nullable();
            $table->string('social_status', 50)->nullable();
            $table->string('residency', 50)->nullable();
            $table->string('citizenship', 100)->nullable();
            $table->string('nationality', 100)->nullable();
            $table->string('employment', 100)->nullable();
            $table->string('employer_name', 100)->nullable();
            $table->string('education', 100)->nullable();
            $table->string('business_name', 100)->nullable();
            $table->decimal('income_available', 15, 2)->nullable();
            $table->decimal('monthly_expenses', 15, 2)->nullable();
            $table->string('negative_status_of_individual', 50)->nullable();
            $table->string('tax_identification_number', 50)->nullable();
            $table->string('passport_number', 50)->nullable();
            $table->string('passport_issuer_country', 100)->nullable();
            $table->string('driving_license_number', 50)->nullable();
            $table->string('voters_id', 50)->nullable();
            $table->string('custom_id_number_1', 50)->nullable();
            $table->string('custom_id_number_2', 50)->nullable();
            $table->string('main_address')->nullable();
            $table->string('street', 100)->nullable();
            $table->string('number_of_building', 20)->nullable();
            $table->string('postal_code', 20)->nullable();
            $table->string('region', 100)->nullable();
            $table->string('district', 100)->nullable();
            $table->string('country', 100)->nullable();
            $table->string('mobile_phone', 20)->nullable();
            $table->string('fixed_line', 20)->nullable();
            $table->string('trade_name', 100)->nullable();
            $table->string('legal_form', 50)->nullable();
            $table->date('establishment_date')->nullable();
            $table->string('registration_country', 100)->nullable();
            $table->string('industry_sector', 100)->nullable();
            $table->string('registration_number', 50)->nullable();
            $table->string('middle_names', 100)->nullable();
            $table->string('member_number', 50)->nullable();
            $table->string('contact_number', 20)->nullable();
            $table->string('occupation', 100)->nullable();
            $table->string('education_level', 100)->nullable();
            $table->integer('dependent_count')->nullable();
            $table->decimal('annual_income', 15, 2)->nullable();
            $table->string('city', 100)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('religion', 50)->nullable();
            $table->string('building_number', 20)->nullable();
            $table->string('ward', 100)->nullable();
            $table->boolean('accept_terms')->nullable();
            $table->string('application_type', 50)->nullable();
            $table->string('guarantor_region', 100)->nullable();
            $table->string('guarantor_ward', 100)->nullable();
            $table->string('guarantor_district', 100)->nullable();
            $table->string('guarantor_relationship', 100)->nullable();
            $table->string('guarantor_membership_number', 50)->nullable();
            $table->string('guarantor_full_name', 100)->nullable();
            $table->string('guarantor_email', 100)->nullable();
            $table->string('barua', 10)->nullable();
            $table->string('uthibitisho', 10)->nullable();
            $table->decimal('hisa', 15, 2)->nullable();
            $table->decimal('akiba', 15, 2)->nullable();
            $table->decimal('amana', 15, 2)->nullable();
            $table->string('guarantor_first_name', 100)->nullable();
            $table->string('guarantor_middle_name', 100)->nullable();
            $table->string('guarantor_last_name', 100)->nullable();
            $table->string('income_source', 100)->nullable();
            $table->string('share_payment_status', 50)->nullable();
            $table->decimal('basic_salary', 15, 2)->nullable();
            $table->decimal('gross_salary', 15, 2)->nullable();
            $table->decimal('tax_paid', 15, 2)->nullable();
            $table->decimal('pension', 15, 2)->nullable();
            $table->decimal('nhif', 15, 2)->nullable();
            $table->string('workers_union_etc', 50)->nullable();
            $table->string('member_category', 50)->nullable();
            $table->string('guarantor_phone', 20)->nullable();
            $table->string('id_type', 50)->nullable();
            $table->boolean('portal_access_enabled')->default(false);
            $table->string('password_hash')->nullable();
            $table->timestamp('portal_registered_at')->nullable();
            $table->string('password_reset_token')->nullable();
            $table->timestamp('password_reset_expires_at')->nullable();
            $table->timestamp('last_portal_login_at')->nullable();
            $table->string('portal_session_token')->nullable();
            $table->timestamp('portal_session_expires_at')->nullable();
            $table->softDeletes();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->string('employee_id')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->text('payment_link')->nullable();
            $table->index(['client_number']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('clients');
    }
};