<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('share_withdrawals', function (Blueprint $table) {
            $table->id();

            // Foreign key to the member/client
            $table->unsignedBigInteger('member_id')->nullable();
            $table->string('client_number', 10)->nullable(); // Redundant but good for quick lookup

            // Foreign key to the share product/type
            $table->unsignedBigInteger('product_id')->nullable();
            $table->string('product_name')->nullable(); // For snapshot purposes

            // Number of shares withdrawn
            $table->integer('withdrawal_amount')->nullable();

            // Financials
            $table->decimal('nominal_price', 15, 2)->nullable();
            $table->decimal('total_value', 20, 2)->nullable(); // withdrawal_amount * nominal_price

            // Account Information
            $table->unsignedBigInteger('receiving_account_id')->nullable();
            $table->string('receiving_account_number')->nullable();
            $table->unsignedBigInteger('source_account_id')->nullable();
            $table->string('source_account_number')->nullable();

            // Reason for withdrawal
            $table->text('reason')->nullable();

            // Status and Approval tracking
            $table->string('status')->default('PENDING')->nullable(); // PENDING, APPROVED, REJECTED, COMPLETED
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->unsignedBigInteger('created_by')->nullable(); // admin/staff user ID who created the request

            // Timestamps
            $table->timestamps();
            $table->softDeletes(); // Add soft deletes for record keeping
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('share_withdrawals');
    }
}; 