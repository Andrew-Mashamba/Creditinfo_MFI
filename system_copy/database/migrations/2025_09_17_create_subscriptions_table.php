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
        Schema::create('subscriptions', function (Blueprint $table) {
            $table->id();
            
            // Service Information
            $table->string('service_name');
            $table->string('service_code', 10)->unique();
            $table->text('description')->nullable();
            $table->enum('service_type', ['sms', 'email', 'control_numbers', 'payment_links', 'mobile_app', 'members_portal', 'ai_assistant', 'crb_integration', 'general'])->default('general');
            $table->enum('subscription_type', ['mandatory', 'optional'])->default('optional');
            
            // Pricing Configuration
            $table->decimal('base_price', 15, 2)->default(0);
            $table->decimal('cost_per_unit', 10, 2)->default(0);
            $table->string('unit_type')->nullable(); // 'sms', 'email', 'user', 'link', etc.
            $table->integer('included_units')->default(0);
            $table->enum('pricing_model', ['fixed', 'usage_based', 'tiered', 'free'])->default('fixed');
            
            // Subscription Management
            $table->enum('status', ['active', 'paused', 'cancelled', 'expired'])->default('active');
            $table->enum('billing_frequency', ['monthly', 'quarterly', 'annually', 'one_time'])->default('monthly');
            $table->date('start_date');
            $table->date('end_date')->nullable();
            $table->date('next_billing_date')->nullable();
            $table->date('last_billed_date')->nullable();
            
            // Usage Tracking
            $table->bigInteger('current_usage')->default(0);
            $table->bigInteger('total_usage')->default(0);
            $table->decimal('current_month_cost', 15, 2)->default(0);
            $table->decimal('total_cost_paid', 15, 2)->default(0);
            
            // Configuration
            $table->json('features')->nullable(); // Array of features
            $table->json('configuration')->nullable(); // Service-specific config
            $table->boolean('auto_renew')->default(true);
            $table->boolean('is_system_service')->default(false); // System-managed vs user-managed
            
            // Integration with Trade Payables
            $table->bigInteger('trade_payable_id')->nullable(); // Link to trade_payables table
            $table->string('vendor_name')->nullable();
            $table->string('vendor_email')->nullable();
            $table->string('vendor_phone')->nullable();
            
            // Audit Fields
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('updated_by')->nullable();
            $table->bigInteger('branch_id')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['service_type', 'status']);
            $table->index(['status', 'next_billing_date']);
            $table->index(['billing_frequency', 'status']);
            $table->index(['trade_payable_id']);
            $table->index(['created_at']);
            
            // Foreign Keys
            $table->foreign('created_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('updated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('branch_id')->references('id')->on('branches')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('subscriptions');
    }
};
