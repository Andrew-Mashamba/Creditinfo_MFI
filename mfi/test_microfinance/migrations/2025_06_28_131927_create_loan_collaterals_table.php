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
        Schema::create('loan_collaterals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('loan_guarantor_id')->constrained('loan_guarantors')->onDelete('cascade');
            $table->enum('collateral_type', ['savings', 'deposits', 'shares', 'physical']);
            $table->foreignId('account_id')->nullable()->constrained('accounts')->onDelete('restrict'); // For financial collaterals
            $table->decimal('collateral_amount', 15, 2); // Amount being used as collateral
            $table->decimal('account_balance', 15, 2)->nullable(); // Current balance at time of collateralization
            $table->decimal('locked_amount', 15, 2)->default(0); // Amount currently locked
            $table->decimal('available_amount', 15, 2)->default(0); // Amount available for withdrawal
            
            // Physical collateral details
            $table->string('physical_collateral_id')->nullable();
            $table->string('physical_collateral_description')->nullable();
            $table->string('physical_collateral_location')->nullable();
            $table->string('physical_collateral_owner_name')->nullable();
            $table->string('physical_collateral_owner_nida')->nullable();
            $table->string('physical_collateral_owner_contact')->nullable();
            $table->string('physical_collateral_owner_address')->nullable();
            $table->decimal('physical_collateral_value', 15, 2)->nullable();
            $table->date('physical_collateral_valuation_date')->nullable();
            $table->string('physical_collateral_valuation_method')->nullable();
            $table->string('physical_collateral_valuer_name')->nullable();
            
            // Insurance details for physical collateral
            $table->string('insurance_policy_number')->nullable();
            $table->string('insurance_company_name')->nullable();
            $table->text('insurance_coverage_details')->nullable();
            $table->date('insurance_expiration_date')->nullable();
            
            // Status and tracking
            $table->enum('status', ['active', 'inactive', 'released', 'forfeited'])->default('active');
            $table->timestamp('collateral_start_date')->useCurrent();
            $table->timestamp('collateral_end_date')->nullable();
            $table->text('notes')->nullable();
            $table->boolean('is_active')->default(true);
            $table->timestamps();
            $table->softDeletes();
            
            // Indexes for performance
            $table->index(['loan_guarantor_id', 'status']);
            $table->index(['account_id', 'status']);
            $table->index(['collateral_type', 'status']);
            $table->index(['physical_collateral_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_collaterals');
    }
};
