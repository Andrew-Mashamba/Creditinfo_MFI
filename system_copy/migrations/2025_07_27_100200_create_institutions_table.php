<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for institutions table
 * 
 * Combined from these migrations:
 * - 2024_03_13_100001_create_institutions_table.php
 * - 2025_01_27_120000_add_depreciation_accounts_to_institutions_table.php
 * - 2025_06_05_170000_add_missing_columns_to_institutions_table.php
 * - 2025_06_05_180001_recreate_status_columns_in_institutions_table.php
 * - 2025_06_05_180002_force_recreate_status_columns_in_institutions_table.php
 * - 2025_07_09_181428_add_main_accounts_to_institutions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('institutions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code');
            $table->text('description')->nullable();
            $table->json('settings')->nullable();
            $table->string('contact_email')->nullable();
            $table->string('contact_phone')->nullable();
            $table->text('address')->nullable();
            $table->string('logo_url')->nullable();
            $table->softDeletes();
            $table->string('depreciation_expense_account')->nullable();
            $table->string('accumulated_depreciation_account')->nullable();
            $table->string('property_and_equipment_account')->nullable();
            $table->string('operations_account')->nullable();
            $table->string('mandatory_shares_account')->nullable();
            $table->string('mandatory_savings_account')->nullable();
            $table->string('mandatory_deposits_account')->nullable();
            $table->string('members_external_loans_crealance')->nullable();
            $table->string('institution_id')->nullable();
            $table->string('institution_name')->nullable();
            $table->string('region')->nullable();
            $table->string('wilaya')->nullable();
            $table->string('phone_number')->nullable();
            $table->string('email')->nullable();
            $table->string('imgUrl')->nullable();
            $table->string('admin_name')->nullable();
            $table->integer('available_shares')->nullable();
            $table->decimal('registration_fees', 15, 2)->nullable();
            $table->integer('min_shares')->nullable();
            $table->string('initial_shares')->nullable();
            $table->string('temp_shares_holding_account')->nullable();
            $table->string('value_per_share')->nullable();
            $table->boolean('selected')->nullable();
            $table->boolean('inactivity')->nullable();
            $table->integer('allocated_shares')->nullable();
            $table->string('admin_email')->nullable();
            $table->string('manager_email')->nullable();
            $table->string('tin_number')->nullable();
            $table->string('tcdc_form')->nullable();
            $table->string('microfinance_license')->nullable();
            $table->string('manager_name')->nullable();
            $table->integer('total_shares')->nullable();
            $table->boolean('settings_status')->nullable();
            $table->string('repayment_frequency')->nullable();
            $table->date('startDate')->nullable();
            $table->string('db_host')->nullable();
            $table->string('db_port')->nullable();
            $table->string('db_name')->nullable();
            $table->string('db_username')->nullable();
            $table->string('db_password')->nullable();
            $table->text('notes')->nullable();
            $table->string('onboarding_process')->nullable();
            $table->decimal('petty_amount_limit', 15, 2)->nullable();
            $table->string('status')->nullable();
            $table->string('institution_status')->nullable();
            $table->string('main_vaults_account')->nullable();
            $table->string('main_till_account')->nullable();
            $table->string('main_petty_cash_account')->nullable();
            $table->index(['code']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('institutions');
    }
};