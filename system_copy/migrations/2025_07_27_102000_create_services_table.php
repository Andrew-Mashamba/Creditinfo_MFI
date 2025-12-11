<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for services table
 * 
 * Combined from these migrations:
 * - 2025_05_09_130548_create_services_table.php
 * - 2025_05_24_123115_add_payment_mode_to_services_table.php
 * - 2025_05_24_132712_add_is_recurring_to_services_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('services', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('code', 3);
            $table->text('description')->nullable();
            $table->boolean('is_mandatory')->default(false);
            $table->decimal('lower_limit', 15, 2)->nullable();
            $table->decimal('upper_limit', 15, 2)->nullable();
            $table->boolean('isRecurring')->default(false);
            $table->string('paymentMode', 1)->default('3');
            $table->string('debit_account')->nullable();
            $table->string('credit_account')->nullable();
            $table->string('payment_mode')->default('2');
            $table->boolean('is_recurring')->default(false);
            $table->index(['debit_account']);
            $table->index(['credit_account']);
            $table->index(['code']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('services');
    }
};