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
        Schema::create('budget_allocations', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budget_managements')->onDelete('cascade');
            $table->enum('allocation_type', ['MONTHLY', 'QUARTERLY', 'CUSTOM', 'ANNUAL'])->default('MONTHLY');
            $table->integer('period'); // Month (1-12) or Quarter (1-4)
            $table->integer('year');
            $table->decimal('allocated_amount', 20, 2)->default(0);
            $table->decimal('utilized_amount', 20, 2)->default(0);
            $table->decimal('available_amount', 20, 2)->default(0);
            $table->decimal('rollover_amount', 20, 2)->default(0); // From previous period
            $table->decimal('advance_amount', 20, 2)->default(0); // Borrowed from future
            $table->decimal('transferred_in', 20, 2)->default(0); // Received from transfers
            $table->decimal('transferred_out', 20, 2)->default(0); // Sent to other budgets
            $table->decimal('supplementary_amount', 20, 2)->default(0); // Additional allocation
            $table->decimal('percentage', 5, 2)->default(8.33); // Percentage of annual budget
            $table->text('notes')->nullable();
            $table->enum('rollover_policy', ['AUTOMATIC', 'APPROVAL_REQUIRED', 'NO_ROLLOVER'])->default('APPROVAL_REQUIRED');
            $table->boolean('is_locked')->default(false);
            $table->timestamp('locked_at')->nullable();
            $table->foreignId('locked_by')->nullable()->constrained('users');
            $table->timestamps();
            
            // Unique constraint to prevent duplicate allocations
            $table->unique(['budget_id', 'period', 'year', 'allocation_type']);
            
            // Index for performance
            $table->index(['budget_id', 'year']);
            $table->index(['period', 'year']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_allocations');
    }
};