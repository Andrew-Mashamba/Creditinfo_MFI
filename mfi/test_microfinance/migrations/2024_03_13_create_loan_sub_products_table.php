<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_sub_products', function (Blueprint $table) {
            $table->id();
            $table->string('sub_product_id', 255)->nullable();
            $table->string('product_id', 255)->nullable();
            $table->string('sub_product_name', 255)->nullable();
            $table->string('prefix', 255)->nullable();
            $table->string('sub_product_status', 255)->nullable();
            $table->string('currency', 255)->nullable();
            $table->bigInteger('disbursement_account')->nullable();
            $table->bigInteger('collection_account_loan_interest')->nullable();
            $table->bigInteger('collection_account_loan_principle')->nullable();
            $table->bigInteger('collection_account_loan_charges')->nullable();
            $table->bigInteger('collection_account_loan_penalties')->nullable();
            $table->double('principle_min_value')->nullable();
            $table->double('principle_max_value')->nullable();
            $table->double('min_term')->nullable();
            $table->double('max_term')->nullable();
            $table->string('interest_value', 255)->nullable();
            $table->string('interest_tenure', 255)->nullable();
            $table->string('principle_grace_period', 255)->nullable();
            $table->string('interest_grace_period', 255)->nullable();
            $table->string('interest_method', 255)->nullable();
            $table->string('amortization_method', 255)->nullable();
            $table->integer('days_in_a_year')->nullable();
            $table->integer('days_in_a_month')->nullable();
            $table->string('repayment_strategy', 255)->nullable();
            $table->double('maintenance_fees_value')->nullable();
            $table->string('ledger_fees', 255)->nullable();
            $table->double('ledger_fees_value')->nullable();
            $table->string('lock_guarantee_funds', 255)->nullable();
            $table->string('maintenance_fees', 255)->nullable();
            $table->integer('inactivity')->nullable();
            $table->string('requires_approval', 255)->nullable();
            $table->string('allow_statement_generation', 255)->nullable();
            $table->string('send_notifications', 255)->nullable();
            $table->string('require_image_member', 255)->nullable();
            $table->string('require_image_id', 255)->nullable();
            $table->string('require_mobile_number', 255)->nullable();
            $table->string('notes', 255)->nullable();
            $table->string('institution_id', 255)->nullable();
            $table->string('loan_product_account', 150)->nullable();
            $table->string('charges', 150)->nullable();
            $table->string('loan_multiplier', 100)->nullable();
            $table->string('ltv', 100)->nullable();
            $table->string('score_limit', 100)->nullable();
            $table->string('repayment_frequency', 255)->nullable();
            $table->string('interest_account', 150)->nullable();
            $table->string('fees_account', 150)->nullable();
            $table->string('payable_account', 150)->nullable();
            $table->string('insurance_account', 150)->nullable();
            $table->string('loan_interest_account', 255)->nullable();
            $table->string('loan_charges_account', 255)->nullable();
            $table->string('loan_insurance_account', 255)->nullable();
            $table->string('charge_product_account', 250)->nullable();
            $table->string('insurance_product_account', 250)->nullable();
            $table->string('penalty_value', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_sub_products');
    }
}; 