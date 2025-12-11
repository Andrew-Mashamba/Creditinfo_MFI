<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('general_ledger', function (Blueprint $table) {
            $table->id();
            // Removed institution_id column
            $table->string('record_on_account_number', 255)->nullable();
            $table->double('record_on_account_number_balance')->nullable();
            $table->bigInteger('sender_branch_id')->nullable();
            $table->bigInteger('beneficiary_branch_id')->nullable();
            $table->bigInteger('sender_product_id')->nullable();
            $table->bigInteger('sender_sub_product_id')->nullable();
            $table->bigInteger('beneficiary_product_id')->nullable();
            $table->bigInteger('beneficiary_sub_product_id')->nullable();
            $table->bigInteger('sender_id')->nullable();
            $table->bigInteger('beneficiary_id')->nullable();
            $table->string('sender_name', 255)->nullable();
            $table->string('beneficiary_name', 255)->nullable();
            $table->string('sender_account_number', 255)->nullable();
            $table->string('beneficiary_account_number', 255)->nullable();
            $table->string('transaction_type', 100)->nullable();
            $table->string('sender_account_currency_type', 100)->nullable();
            $table->string('beneficiary_account_currency_type', 100)->nullable();
            $table->text('narration')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->double('credit')->nullable();
            $table->double('debit')->nullable();
            $table->string('reference_number', 255)->nullable();
            $table->string('trans_status', 100)->nullable();
            $table->text('trans_status_description')->nullable();
            $table->string('swift_code', 255)->nullable();
            $table->string('destination_bank_name', 255)->nullable();
            $table->string('destination_bank_number', 255)->nullable();
            $table->bigInteger('partner_bank')->nullable();
            $table->string('partner_bank_name', 255)->nullable();
            $table->string('partner_bank_account_number', 255)->nullable();
            $table->string('partner_bank_transaction_reference_number', 255)->nullable();
            $table->string('payment_status', 100)->nullable();
            $table->string('recon_status', 100)->nullable();
            $table->string('loan_id', 255)->nullable();
            $table->string('bank_reference_number', 255)->nullable();
            $table->string('product_number', 255)->nullable();
            $table->string('major_category_code', 255)->nullable();
            $table->string('category_code', 255)->nullable();
            $table->string('sub_category_code', 255)->nullable();
            $table->string('gl_balance', 150)->nullable();
            $table->string('account_level', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('general_ledger');
    }
}; 