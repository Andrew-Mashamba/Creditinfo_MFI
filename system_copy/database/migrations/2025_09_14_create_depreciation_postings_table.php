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
        Schema::create('depreciation_postings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('asset_id');
            $table->integer('period_year');
            $table->integer('period_month');
            $table->string('period_type', 20); // 'monthly', 'quarterly', 'yearly'
            $table->decimal('amount_posted', 15, 2);
            $table->date('posting_date');
            $table->string('reference_number', 100)->nullable();
            $table->unsignedBigInteger('posted_by');
            $table->text('journal_entries')->nullable(); // JSON of entry IDs
            $table->string('status', 20)->default('posted'); // posted, reversed
            $table->timestamps();
            
            // Indexes
            $table->index(['asset_id', 'period_year', 'period_month']);
            $table->unique(['asset_id', 'period_year', 'period_month', 'period_type']);
            $table->index('reference_number');
            $table->index('posting_date');
            
            // Foreign keys
            $table->foreign('asset_id')->references('id')->on('ppes')->onDelete('cascade');
            $table->foreign('posted_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('depreciation_postings');
    }
};