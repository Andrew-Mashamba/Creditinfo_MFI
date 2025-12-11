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
        Schema::create('loan_loss_reserves', function (Blueprint $table) {
            $table->id();
            $table->integer('year');
            $table->integer('month')->nullable();
            $table->decimal('profits', 20, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('reserve_amount', 20, 2);
            $table->decimal('initial_allocation', 20, 2);
            $table->decimal('total_allocation', 20, 2);
            $table->decimal('actual_losses', 20, 2)->nullable();
            $table->decimal('adjustments', 20, 2)->nullable();
            $table->string('calculation_method', 50)->nullable(); // ecl, aging, manual
            $table->string('status', 50)->default('allocated'); // allocated, finalized, adjusted
            $table->text('notes')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->bigInteger('approved_by')->nullable();
            $table->timestamp('approved_at')->nullable();
            $table->timestamps();
            
            // Indexes
            $table->index(['year', 'month']);
            $table->index('status');
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('loan_loss_reserves');
    }
};