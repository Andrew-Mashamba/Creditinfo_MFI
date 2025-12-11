<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up(): void
    {
        // Add enhanced fields to existing ppes table
        Schema::table('ppes', function (Blueprint $table) {
            // Asset identification and tracking
            $table->string('asset_code')->unique()->nullable()->after('id');
            $table->string('barcode')->nullable()->after('asset_code');
            $table->string('serial_number')->nullable()->after('barcode');
            $table->string('manufacturer')->nullable()->after('serial_number');
            $table->string('model')->nullable()->after('manufacturer');
            
            // Depreciation methods
            $table->enum('depreciation_method', ['straight_line', 'declining_balance', 'sum_of_years', 'units_of_production'])->default('straight_line')->after('depreciation_rate');
            $table->decimal('units_produced', 15, 2)->nullable()->after('depreciation_method');
            $table->decimal('total_units_expected', 15, 2)->nullable()->after('units_produced');
            
            // Asset condition and valuation
            $table->enum('condition', ['excellent', 'good', 'fair', 'poor', 'needs_repair'])->default('good')->after('status');
            $table->decimal('market_value', 15, 2)->nullable()->after('closing_value');
            $table->decimal('replacement_cost', 15, 2)->nullable()->after('market_value');
            $table->date('last_valuation_date')->nullable()->after('replacement_cost');
            $table->string('valuation_by')->nullable()->after('last_valuation_date');
            
            // Warranty and insurance
            $table->date('warranty_start_date')->nullable()->after('purchase_date');
            $table->date('warranty_end_date')->nullable()->after('warranty_start_date');
            $table->string('warranty_provider')->nullable()->after('warranty_end_date');
            $table->string('warranty_terms')->nullable()->after('warranty_provider');
            
            // Department and responsibility
            $table->unsignedBigInteger('department_id')->nullable()->after('location');
            $table->unsignedBigInteger('custodian_id')->nullable()->after('department_id');
            $table->unsignedBigInteger('assigned_to')->nullable()->after('custodian_id');
            
            // Tracking fields
            $table->timestamp('last_maintenance_date')->nullable();
            $table->timestamp('next_maintenance_date')->nullable();
            $table->timestamp('last_inspection_date')->nullable();
            $table->timestamp('next_inspection_date')->nullable();
            
            // Asset usage tracking
            $table->decimal('usage_hours', 15, 2)->nullable()->default(0);
            $table->decimal('mileage', 15, 2)->nullable()->default(0);
            $table->integer('usage_cycles')->nullable()->default(0);
            
            // Parent asset for component tracking
            $table->unsignedBigInteger('parent_asset_id')->nullable();
            $table->boolean('is_component')->default(false);
            
            // Additional financial tracking
            $table->decimal('maintenance_cost_to_date', 15, 2)->nullable()->default(0);
            $table->decimal('expected_annual_maintenance', 15, 2)->nullable()->default(0);
            
            // Indexes for performance
            $table->index('asset_code');
            $table->index('department_id');
            $table->index('custodian_id');
            $table->index('parent_asset_id');
            $table->index('status');
            $table->index('condition');
        });

        // Create PPE maintenance records table
        Schema::create('ppe_maintenance_records', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->enum('maintenance_type', ['preventive', 'corrective', 'emergency', 'inspection', 'calibration']);
            $table->date('maintenance_date');
            $table->string('performed_by');
            $table->string('vendor_name')->nullable();
            $table->text('description');
            $table->text('parts_replaced')->nullable();
            $table->decimal('cost', 15, 2)->default(0);
            $table->decimal('downtime_hours', 10, 2)->nullable();
            $table->date('next_maintenance_date')->nullable();
            $table->enum('status', ['scheduled', 'in_progress', 'completed', 'cancelled'])->default('scheduled');
            $table->text('notes')->nullable();
            $table->string('work_order_number')->nullable();
            $table->string('invoice_number')->nullable();
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['ppe_id', 'maintenance_date']);
            $table->index('status');
        });

        // Create PPE transfer/movement history table
        Schema::create('ppe_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->string('from_location')->nullable();
            $table->string('to_location');
            $table->unsignedBigInteger('from_department_id')->nullable();
            $table->unsignedBigInteger('to_department_id')->nullable();
            $table->unsignedBigInteger('from_custodian_id')->nullable();
            $table->unsignedBigInteger('to_custodian_id')->nullable();
            $table->date('transfer_date');
            $table->string('reason');
            $table->string('approved_by')->nullable();
            $table->text('notes')->nullable();
            $table->string('transfer_document_number')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'completed'])->default('pending');
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['ppe_id', 'transfer_date']);
            $table->index('status');
        });

        // Create PPE revaluation history table
        Schema::create('ppe_revaluations', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->date('revaluation_date');
            $table->decimal('old_value', 15, 2);
            $table->decimal('new_value', 15, 2);
            $table->decimal('revaluation_amount', 15, 2);
            $table->enum('revaluation_type', ['appreciation', 'impairment', 'market_adjustment']);
            $table->string('performed_by');
            $table->string('approved_by')->nullable();
            $table->text('reason');
            $table->text('supporting_documents')->nullable();
            $table->string('valuation_method')->nullable();
            $table->enum('status', ['pending', 'approved', 'rejected', 'posted'])->default('pending');
            $table->string('journal_entry_reference')->nullable();
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['ppe_id', 'revaluation_date']);
            $table->index('status');
        });

        // Create PPE insurance table
        Schema::create('ppe_insurance', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->string('policy_number');
            $table->string('insurance_company');
            $table->enum('coverage_type', ['comprehensive', 'fire', 'theft', 'damage', 'liability', 'other']);
            $table->decimal('insured_value', 15, 2);
            $table->decimal('premium_amount', 15, 2);
            $table->date('start_date');
            $table->date('end_date');
            $table->decimal('deductible', 15, 2)->nullable();
            $table->text('coverage_details')->nullable();
            $table->string('agent_name')->nullable();
            $table->string('agent_contact')->nullable();
            $table->enum('status', ['active', 'expired', 'cancelled', 'claimed'])->default('active');
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['ppe_id', 'status']);
            $table->index(['start_date', 'end_date']);
        });

        // Create PPE audit trail table
        Schema::create('ppe_audit_trails', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->string('action');
            $table->json('old_values')->nullable();
            $table->json('new_values')->nullable();
            $table->unsignedBigInteger('user_id');
            $table->string('user_name');
            $table->string('ip_address')->nullable();
            $table->text('notes')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->foreign('user_id')->references('id')->on('users');
            $table->index(['ppe_id', 'created_at']);
            $table->index('action');
        });

        // Create PPE documents table for attachments
        Schema::create('ppe_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->enum('document_type', [
                'purchase_invoice', 'receipt', 'warranty', 'manual', 
                'insurance_policy', 'valuation_report', 'maintenance_report',
                'inspection_report', 'disposal_document', 'photo', 'other'
            ]);
            $table->string('document_name');
            $table->string('file_path');
            $table->string('file_size')->nullable();
            $table->string('mime_type')->nullable();
            $table->text('description')->nullable();
            $table->date('document_date')->nullable();
            $table->string('uploaded_by');
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['ppe_id', 'document_type']);
        });

        // Create PPE components table for tracking sub-assets
        Schema::create('ppe_components', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('parent_ppe_id');
            $table->unsignedBigInteger('component_ppe_id');
            $table->date('installation_date');
            $table->date('removal_date')->nullable();
            $table->string('installed_by')->nullable();
            $table->string('removed_by')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();
            
            $table->foreign('parent_ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->foreign('component_ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->index(['parent_ppe_id', 'component_ppe_id']);
        });

        // Create depreciation schedule table
        Schema::create('ppe_depreciation_schedule', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('ppe_id');
            $table->integer('period_year');
            $table->integer('period_month');
            $table->decimal('opening_value', 15, 2);
            $table->decimal('depreciation_amount', 15, 2);
            $table->decimal('closing_value', 15, 2);
            $table->decimal('accumulated_depreciation', 15, 2);
            $table->boolean('is_posted')->default(false);
            $table->string('journal_reference')->nullable();
            $table->date('posting_date')->nullable();
            $table->timestamps();
            
            $table->foreign('ppe_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->unique(['ppe_id', 'period_year', 'period_month']);
            $table->index(['period_year', 'period_month']);
            $table->index('is_posted');
        });
    }

    public function down(): void
    {
        // Drop new tables
        Schema::dropIfExists('ppe_depreciation_schedule');
        Schema::dropIfExists('ppe_components');
        Schema::dropIfExists('ppe_documents');
        Schema::dropIfExists('ppe_audit_trails');
        Schema::dropIfExists('ppe_insurance');
        Schema::dropIfExists('ppe_revaluations');
        Schema::dropIfExists('ppe_transfers');
        Schema::dropIfExists('ppe_maintenance_records');
        
        // Remove added columns from ppes table
        Schema::table('ppes', function (Blueprint $table) {
            $table->dropColumn([
                'asset_code', 'barcode', 'serial_number', 'manufacturer', 'model',
                'depreciation_method', 'units_produced', 'total_units_expected',
                'condition', 'market_value', 'replacement_cost', 'last_valuation_date', 'valuation_by',
                'warranty_start_date', 'warranty_end_date', 'warranty_provider', 'warranty_terms',
                'department_id', 'custodian_id', 'assigned_to',
                'last_maintenance_date', 'next_maintenance_date', 'last_inspection_date', 'next_inspection_date',
                'usage_hours', 'mileage', 'usage_cycles',
                'parent_asset_id', 'is_component',
                'maintenance_cost_to_date', 'expected_annual_maintenance'
            ]);
        });
    }
};