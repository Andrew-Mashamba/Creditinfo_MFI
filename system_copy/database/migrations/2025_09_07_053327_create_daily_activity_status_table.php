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
        Schema::create('daily_activity_status', function (Blueprint $table) {
            $table->id();
            $table->string('activity_key')->unique();
            $table->string('activity_name');
            $table->enum('status', ['pending', 'running', 'completed', 'failed', 'skipped'])->default('pending');
            $table->decimal('progress', 5, 2)->default(0); // 0-100
            $table->integer('total_records')->default(0);
            $table->integer('processed_records')->default(0);
            $table->integer('failed_records')->default(0);
            $table->json('metadata')->nullable();
            $table->text('last_error')->nullable();
            $table->timestamp('started_at')->nullable();
            $table->timestamp('completed_at')->nullable();
            $table->date('process_date')->default(now()->toDateString());
            $table->integer('execution_time_seconds')->nullable();
            $table->string('triggered_by')->nullable(); // manual, scheduled, system
            $table->timestamps();
            
            $table->index(['activity_key', 'process_date']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('daily_activity_status');
    }
};