<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('share_registers', function (Blueprint $table) {
            $table->id();
            
            // Institution and Branch
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->unsignedBigInteger('branch_id')->nullable();
            
            // Member Information
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('member_number', 50)->nullable();
            $table->string('member_name', 255)->nullable();
            
            // Share Product Information
            $table->unsignedBigInteger('product_id')->nullable(); // References sub_products
            $table->string('product_name', 100)->nullable();
            $table->string('product_type', 50)->nullable(); // e.g., 'MANDATORY', 'VOLUNTARY', 'PREFERENCE'
            
            // Share Account Details
            $table->string('share_account_number', 50)->unique()->nullable();
            $table->decimal('nominal_price', 20, 6)->nullable(); // Price per share at time of purchase
            $table->decimal('current_price', 20, 6)->nullable(); // Current market price per share
            
            // Share Holdings
            $table->integer('total_shares_issued')->default(0); // Total shares ever issued to this account
            $table->integer('total_shares_redeemed')->default(0); // Total shares redeemed
            $table->integer('total_shares_transferred_in')->default(0); // Total shares received via transfer
            $table->integer('total_shares_transferred_out')->default(0); // Total shares sent via transfer
            $table->integer('current_share_balance')->default(0); // Current balance (issued + transferred_in - redeemed - transferred_out)
            $table->decimal('total_share_value', 20, 6)->default(0); // Current total value (current_share_balance * current_price)
            
            // Dividend Information
            $table->decimal('last_dividend_rate', 10, 4)->nullable(); // Last dividend rate applied
            $table->decimal('last_dividend_amount', 20, 6)->nullable(); // Last dividend amount received
            $table->timestamp('last_dividend_date')->nullable(); // Date of last dividend payment
            $table->decimal('accumulated_dividends', 20, 6)->default(0); // Total dividends earned but not paid
            $table->decimal('total_paid_dividends', 20, 6)->default(0); // Total dividends paid
            $table->decimal('total_pending_dividends', 20, 6)->default(0); // Total dividends pending
            
            // Linked Accounts
            $table->string('linked_savings_account', 50)->nullable(); // Account used for share payments
            $table->string('dividend_payment_account', 50)->nullable(); // Account for receiving dividends
            
            // Status and Dates
            $table->enum('status', ['ACTIVE', 'INACTIVE', 'FROZEN', 'CLOSED'])->default('ACTIVE');
            $table->timestamp('opening_date')->nullable();
            $table->timestamp('last_activity_date')->nullable();
            $table->timestamp('closing_date')->nullable();
            
            // Transaction History
            $table->string('last_transaction_type', 50)->nullable(); // ISSUE, REDEEM, TRANSFER_IN, TRANSFER_OUT
            $table->string('last_transaction_reference', 100)->nullable();
            $table->timestamp('last_transaction_date')->nullable();
            
            // Compliance and Restrictions
            $table->boolean('is_restricted')->default(false); // For shares with transfer restrictions
            $table->text('restriction_notes')->nullable();
            $table->boolean('requires_approval')->default(false); // For shares requiring approval for transfers
            
            // Audit Fields
            $table->unsignedBigInteger('created_by')->nullable();
            $table->unsignedBigInteger('updated_by')->nullable();
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Keys
            // $table->foreign('institution_id')->references('id')->on('institutions');
            // $table->foreign('member_id')->references('id')->on('members');
            // $table->foreign('product_id')->references('id')->on('sub_products');
        });
    }

    public function down()
    {
        Schema::dropIfExists('share_registers');
    }
}; 