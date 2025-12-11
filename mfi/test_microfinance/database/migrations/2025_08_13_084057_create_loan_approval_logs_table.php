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
        Schema::create('loan_approval_logs', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id');
            $table->string('stage', 50);
            $table->string('action', 50);
            $table->text('comment')->nullable();
            $table->unsignedBigInteger('performed_by')->nullable();
            $table->timestamps();
            
            // Indexes for performance
            $table->index('loan_id');
            $table->index(['loan_id', 'stage']);
            $table->index('performed_by');
            
            // Foreign keys
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
            $table->foreign('performed_by')->references('id')->on('users')->onDelete('set null');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_approval_logs');
    }
};