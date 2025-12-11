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
        Schema::create('loan_legal_documents', function (Blueprint $table) {
            $table->id();
            $table->string('document_number', 50)->unique(); // Auto-generated reference
            $table->string('loan_id', 50)->index();
            $table->string('client_number', 50)->nullable();
            $table->string('document_type', 50); // warning_letter, demand_letter, final_notice, legal_notice, court_summons
            $table->integer('days_in_arrears')->default(0); // Days in arrears when generated
            $table->decimal('outstanding_amount', 20, 2)->default(0);
            $table->decimal('principal_outstanding', 20, 2)->default(0);
            $table->decimal('interest_outstanding', 20, 2)->default(0);
            $table->decimal('penalty_outstanding', 20, 2)->default(0);
            $table->date('generated_date');
            $table->date('sent_date')->nullable();
            $table->date('acknowledged_date')->nullable();
            $table->date('response_deadline')->nullable(); // Date by which client must respond
            $table->string('delivery_method', 30)->nullable(); // email, post, hand_delivery, courier
            $table->string('delivery_reference', 100)->nullable(); // Tracking number, email ID
            $table->string('delivery_status', 30)->default('pending'); // pending, sent, delivered, returned, acknowledged
            $table->string('document_path', 255)->nullable(); // Path to PDF file
            $table->text('recipient_address')->nullable();
            $table->text('notes')->nullable();
            $table->string('status', 30)->default('draft'); // draft, approved, sent, acknowledged, escalated, resolved
            $table->foreignId('generated_by')->nullable()->constrained('users')->onDelete('set null');
            $table->foreignId('approved_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamp('approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->boolean('escalated')->default(false);
            $table->timestamp('escalated_at')->nullable();
            $table->string('escalation_reason', 255)->nullable();
            $table->foreignId('branch_id')->nullable()->constrained('branches')->onDelete('restrict');
            $table->timestamps();

            // Indexes
            $table->index(['loan_id', 'document_type']);
            $table->index(['generated_date', 'status']);
            $table->index(['document_type', 'status']);
            $table->index(['days_in_arrears']);
        });

        // Track document escalation history
        Schema::create('loan_legal_document_history', function (Blueprint $table) {
            $table->id();
            $table->foreignId('document_id')->constrained('loan_legal_documents')->onDelete('cascade');
            $table->string('action', 50); // created, approved, sent, delivered, acknowledged, escalated, resolved
            $table->string('old_status', 30)->nullable();
            $table->string('new_status', 30)->nullable();
            $table->text('notes')->nullable();
            $table->foreignId('performed_by')->nullable()->constrained('users')->onDelete('set null');
            $table->timestamps();

            $table->index(['document_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_legal_document_history');
        Schema::dropIfExists('loan_legal_documents');
    }
};
