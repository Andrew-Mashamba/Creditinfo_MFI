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
        if (!Schema::hasTable('scheduled_reports')) {
            Schema::create('scheduled_reports', function (Blueprint $table) {
                $table->id();
                $table->string('report_type', 100);
                $table->json('report_config')->nullable();
                $table->unsignedBigInteger('user_id');
                $table->enum('status', ['scheduled', 'processing', 'completed', 'failed', 'cancelled'])->default('scheduled');
                $table->enum('frequency', ['once', 'daily', 'weekly', 'monthly', 'quarterly', 'annually'])->default('once');
                $table->timestamp('scheduled_at')->nullable();
                $table->timestamp('last_run_at')->nullable();
                $table->timestamp('next_run_at')->nullable();
                $table->text('error_message')->nullable();
                $table->string('output_path')->nullable();
                $table->string('email_recipients')->nullable();
                $table->boolean('email_sent')->default(false);
                $table->integer('retry_count')->default(0);
                $table->integer('max_retries')->default(3);
                $table->timestamps();
                
                // Indexes
                $table->index(['user_id', 'status']);
                $table->index(['report_type', 'status']);
                $table->index(['scheduled_at']);
                $table->index(['next_run_at']);
                
                // Foreign key constraint
                $table->foreign('user_id')->references('id')->on('users')->onDelete('cascade');
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('scheduled_reports');
    }
};
