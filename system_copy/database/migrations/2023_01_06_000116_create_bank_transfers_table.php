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
        Schema::create('bank_transfers', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('vault_id');
            $table->decimal('amount', 15, 2);
            $table->enum('reason', ['over_limit', 'manual', 'scheduled'])->default('over_limit');
            $table->enum('status', ['pending', 'completed', 'failed'])->default('pending');
            $table->string('reference')->unique();
            $table->unsignedBigInteger('initiated_by')->nullable();
            $table->unsignedBigInteger('approved_by')->nullable();
            $table->text('bank_response')->nullable();
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            // Foreign key constraints
            $table->foreign('vault_id')->references('id')->on('vaults')->onDelete('cascade');
            $table->foreign('initiated_by')->references('id')->on('users')->onDelete('set null');
            $table->foreign('approved_by')->references('id')->on('users')->onDelete('set null');
            
            // Indexes for better performance
            $table->index('vault_id');
            $table->index('status');
            $table->index('reason');
            $table->index('initiated_by');
            $table->index('approved_by');
            $table->index('created_at');
            $table->index(['vault_id', 'status']);
            $table->index(['vault_id', 'created_at']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bank_transfers');
    }
}; 