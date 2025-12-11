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
        if (!Schema::hasTable('budget_alerts')) {
            Schema::create('budget_alerts', function (Blueprint $table) {
            $table->id();
            $table->foreignId('budget_id')->constrained('budget_managements');
            $table->enum('alert_type', [
                'OVER_UTILIZATION',
                'APPROACHING_LIMIT',
                'UNUSUAL_ACTIVITY',
                'ROLLOVER_AVAILABLE',
                'ADVANCE_DUE',
                'PERIOD_CLOSING',
                'LOW_BALANCE'
            ]);
            $table->enum('severity', ['INFO', 'WARNING', 'CRITICAL'])->default('INFO');
            $table->string('alert_title');
            $table->text('alert_message');
            $table->decimal('threshold_percentage', 5, 2)->nullable();
            $table->decimal('current_percentage', 5, 2)->nullable();
            $table->json('alert_data')->nullable();
            $table->boolean('is_read')->default(false);
            $table->boolean('is_resolved')->default(false);
            $table->timestamp('read_at')->nullable();
            $table->foreignId('read_by')->nullable()->constrained('users');
            $table->timestamp('resolved_at')->nullable();
            $table->foreignId('resolved_by')->nullable()->constrained('users');
            $table->text('resolution_notes')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['budget_id', 'alert_type', 'is_resolved']);
            $table->index(['severity', 'is_read']);
            $table->index('created_at');
        });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('budget_alerts');
    }
};