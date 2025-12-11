<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for sub_products table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_sub_products_table.php
 * - 2024_03_14_add_share_settings_to_sub_products.php
 * - 2025_07_05_173454_add_issued_shares_to_sub_products_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('sub_products', function (Blueprint $table) {
            $table->id();
            $table->string('product_name', 50)->nullable();
            $table->string('product_type', 50)->nullable();
            $table->integer('product_id')->nullable();
            $table->bigInteger('savings_type_id')->nullable();
            $table->integer('default_status')->nullable();
            $table->string('sub_product_name', 50)->nullable();
            $table->string('sub_product_id', 50)->nullable();
            $table->bigInteger('deposit_type_id')->nullable();
            $table->bigInteger('share_type_id')->nullable();
            $table->smallInteger('sub_product_status')->nullable();
            $table->string('currency', 50)->nullable();
            $table->smallInteger('deposit')->nullable();
            $table->string('deposit_charge')->nullable(); // Original type: double precision
            $table->string('min_balance')->nullable(); // Original type: double precision
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->string('deposit_charge_min_value')->nullable(); // Original type: double precision
            $table->string('deposit_charge_max_value')->nullable(); // Original type: double precision
            $table->smallInteger('withdraw')->nullable();
            $table->string('withdraw_charge')->nullable(); // Original type: double precision
            $table->string('withdraw_charge_min_value')->nullable(); // Original type: double precision
            $table->string('withdraw_charge_max_value')->nullable(); // Original type: double precision
            $table->string('interest_value')->nullable(); // Original type: double precision
            $table->string('interest_tenure')->nullable(); // Original type: double precision
            $table->string('maintenance_fees')->nullable(); // Original type: double precision
            $table->string('maintenance_fees_value')->nullable(); // Original type: double precision
            $table->string('profit_account')->nullable();
            $table->string('inactivity', 50)->nullable();
            $table->smallInteger('create_during_registration')->nullable();
            $table->smallInteger('activated_by_lower_limit')->nullable();
            $table->smallInteger('requires_approval')->nullable();
            $table->smallInteger('generate_atm_card_profile')->nullable();
            $table->smallInteger('allow_statement_generation')->nullable();
            $table->smallInteger('send_notifications')->nullable();
            $table->smallInteger('require_image_member')->nullable();
            $table->smallInteger('require_image_id')->nullable();
            $table->smallInteger('require_mobile_number')->nullable();
            $table->smallInteger('generate_mobile_profile')->nullable();
            $table->string('notes', 120)->nullable();
            $table->integer('interest')->nullable();
            $table->smallInteger('ledger_fees')->nullable();
            $table->string('ledger_fees_value')->nullable(); // Original type: double precision
            $table->integer('total_shares')->nullable();
            $table->integer('shares_per_member')->nullable();
            $table->string('nominal_price')->nullable(); // Original type: double precision
            $table->string('shares_allocated')->nullable(); // Original type: double precision
            $table->string('available_shares')->nullable(); // Original type: double precision
            $table->bigInteger('branch')->nullable();
            $table->bigInteger('category_code')->nullable();
            $table->bigInteger('sub_category_code')->nullable();
            $table->bigInteger('major_category_code')->nullable();
            $table->string('status', 10)->nullable();
            $table->string('collection_account_withdraw_charges', 30)->nullable();
            $table->string('collection_account_deposit_charges', 30)->nullable();
            $table->string('collection_account_interest_charges', 30)->nullable();
            $table->string('product_account', 120)->nullable();
            $table->integer('minimum_required_shares')->nullable()->default(100);
            $table->integer('lock_in_period')->nullable()->default(30);
            $table->integer('dividend_eligibility_period')->nullable()->default(90);
            $table->string('dividend_payment_frequency', 20)->nullable()->default('annual');
            $table->json('payment_methods')->nullable();
            $table->smallInteger('withdrawal_approval_level')->nullable()->default('1');
            $table->boolean('allow_share_transfer')->nullable()->default(false);
            $table->boolean('allow_share_withdrawal')->nullable()->default(false);
            $table->boolean('enable_dividend_calculation')->nullable()->default(false);
            $table->string('sms_sender_name', 11)->nullable();
            $table->string('sms_api_key')->nullable();
            $table->boolean('sms_enabled')->nullable()->default(false);
            $table->string('issued_shares')->nullable()->default('0'); // Original type: double precision
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('sub_products');
    }
};