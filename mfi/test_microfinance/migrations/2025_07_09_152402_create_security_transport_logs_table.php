<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('security_transport_logs', function (Blueprint $table) {
            $table->id();
            
            // Transfer Reference and Type
            $table->string('transfer_reference')->unique(); // Links to the transfer
            $table->enum('transfer_type', ['BANK_TO_VAULT', 'VAULT_TO_BANK']);
            $table->decimal('amount', 15, 2);
            $table->string('currency', 3)->default('TZS');
            
            // Source and Destination
            $table->unsignedBigInteger('source_vault_id')->nullable();
            $table->unsignedBigInteger('destination_vault_id')->nullable();
            $table->unsignedBigInteger('bank_account_id');
            $table->string('pickup_location');
            $table->string('delivery_location');
            
            // Security Transport Company Details
            $table->string('transport_company_name');
            $table->string('transport_company_license');
            $table->string('transport_company_contact');
            $table->string('insurance_policy_number')->nullable();
            $table->decimal('insurance_coverage_amount', 15, 2)->nullable();
            
            // Vehicle Information
            $table->string('vehicle_registration');
            $table->string('vehicle_type'); // armored van, security vehicle, etc.
            $table->string('vehicle_gps_tracker')->nullable();
            
            // Security Personnel
            $table->json('security_personnel'); // Array of personnel details
            $table->string('team_leader_name');
            $table->string('team_leader_badge');
            $table->string('team_leader_contact');
            
            // Security Seals and Verification
            $table->string('cash_bag_seal_number')->nullable();
            $table->string('container_seal_number')->nullable();
            $table->json('verification_codes')->nullable(); // Multiple verification codes
            
            // Timing and Route
            $table->timestamp('scheduled_pickup_time');
            $table->timestamp('actual_pickup_time')->nullable();
            $table->timestamp('scheduled_delivery_time');
            $table->timestamp('actual_delivery_time')->nullable();
            $table->text('planned_route')->nullable();
            $table->text('actual_route')->nullable();
            
            // Status Tracking
            $table->enum('status', [
                'SCHEDULED', 'IN_TRANSIT', 'DELIVERED', 'CANCELLED', 'DELAYED', 'INCIDENT'
            ])->default('SCHEDULED');
            $table->text('status_notes')->nullable();
            
            // Verification and Signatures
            $table->string('pickup_verified_by')->nullable(); // Staff who verified pickup
            $table->string('delivery_verified_by')->nullable(); // Staff who verified delivery
            $table->timestamp('pickup_verification_time')->nullable();
            $table->timestamp('delivery_verification_time')->nullable();
            $table->text('pickup_notes')->nullable();
            $table->text('delivery_notes')->nullable();
            
            // Incident Reporting
            $table->boolean('has_incident')->default(false);
            $table->text('incident_description')->nullable();
            $table->timestamp('incident_reported_at')->nullable();
            $table->string('incident_report_number')->nullable();
            
            // Audit Trail
            $table->unsignedBigInteger('initiated_by');
            $table->unsignedBigInteger('authorized_by')->nullable();
            $table->json('additional_metadata')->nullable(); // For extensibility
            
            $table->timestamps();
            $table->softDeletes();
            
            // Foreign Key Constraints
            $table->foreign('source_vault_id')->references('id')->on('vaults')->onDelete('set null');
            $table->foreign('destination_vault_id')->references('id')->on('vaults')->onDelete('set null');
            $table->foreign('bank_account_id')->references('id')->on('bank_accounts')->onDelete('cascade');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('cascade');
            $table->foreign('authorized_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes
            $table->index('transfer_reference');
            $table->index('status');
            $table->index('scheduled_pickup_time');
            $table->index('transport_company_name');
            $table->index(['transfer_type', 'status']);
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('security_transport_logs');
    }
};
