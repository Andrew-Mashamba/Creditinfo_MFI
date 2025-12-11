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
        Schema::create('supplementary_requests', function (Blueprint $table) {
            $table->id();
            $table->string('request_number')->unique();
            $table->foreignId('budget_id')->constrained('budget_managements');
            $table->integer('period'); // Month or quarter
            $table->integer('year');
            $table->decimal('current_allocation', 20, 2);
            $table->decimal('requested_amount', 20, 2);
            $table->decimal('approved_amount', 20, 2)->nullable();
            $table->enum('urgency_level', ['LOW', 'MEDIUM', 'HIGH', 'CRITICAL'])->default('MEDIUM');
            $table->text('justification');
            $table->json('supporting_documents')->nullable();
            $table->enum('funding_source', ['RESERVES', 'REALLOCATION', 'EXTERNAL', 'OTHER'])->nullable();
            $table->enum('status', ['DRAFT', 'PENDING', 'UNDER_REVIEW', 'APPROVED', 'REJECTED', 'CANCELLED'])->default('DRAFT');
            $table->foreignId('requested_by')->constrained('users');
            $table->foreignId('department_head_approval')->nullable()->constrained('users');
            $table->timestamp('department_head_approved_at')->nullable();
            $table->foreignId('finance_approval')->nullable()->constrained('users');
            $table->timestamp('finance_approved_at')->nullable();
            $table->foreignId('final_approval')->nullable()->constrained('users');
            $table->timestamp('final_approved_at')->nullable();
            $table->text('approval_notes')->nullable();
            $table->text('rejection_reason')->nullable();
            $table->date('effective_date')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['budget_id', 'period', 'year']);
            $table->index(['status', 'urgency_level']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('supplementary_requests');
    }
};