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
        Schema::create('payment_links', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('loan_id')->nullable();
            $table->string('client_number', 50);
            $table->string('link_id', 100)->nullable();
            $table->string('short_code', 50)->nullable();
            $table->text('payment_url')->nullable();
            $table->text('qr_code_data')->nullable();
            $table->decimal('total_amount', 15, 2)->default(0);
            $table->string('currency', 10)->default('TZS');
            $table->text('description')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->json('response_data')->nullable();
            $table->enum('status', ['ACTIVE', 'EXPIRED', 'USED', 'CANCELLED'])->default('ACTIVE');
            $table->timestamps();
            
            // Indexes
            $table->index('loan_id');
            $table->index('client_number');
            $table->index('link_id');
            $table->index('short_code');
            $table->index('status');
            
            // Foreign key
            $table->foreign('loan_id')->references('id')->on('loans')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('payment_links');
    }
};