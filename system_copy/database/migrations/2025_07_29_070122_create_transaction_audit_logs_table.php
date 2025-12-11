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
        Schema::create('transaction_audit_logs', function (Blueprint $table) {
            $table->id();
            $table->string('action'); // created, updated, deleted, etc.
            $table->string('previous_status')->nullable();
            $table->string('new_status')->nullable();
            $table->text('description');
            $table->json('context')->nullable(); // Additional context data
            $table->unsignedBigInteger('performed_by')->nullable(); // User ID who performed the action
            $table->string('client_ip')->nullable();
            $table->unsignedBigInteger('transaction_id')->nullable(); // Reference to the transaction
            $table->timestamps();
            
            // Add indexes for better performance
            $table->index('action');
            $table->index('performed_by');
            $table->index('transaction_id');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('transaction_audit_logs');
    }
};
