<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for bills table
 * 
 * Combined from these migrations:
 * - 2025_06_08_create_bills_table.php
 * - 2025_06_09_100000_add_payment_link_fields_to_bills_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('bills', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('service_id')->nullable();
            $table->string('control_number')->nullable();
            $table->decimal('amount_due', 10, 2)->nullable();
            $table->decimal('amount_paid', 10, 2)->nullable();
            $table->boolean('is_mandatory')->nullable()->default(false);
            $table->boolean('is_recurring')->nullable()->default(false);
            $table->date('due_date')->nullable();
            $table->string('member_id')->nullable();
            $table->bigInteger('created_by')->nullable();
            $table->string('client_number')->nullable();
            $table->string('payment_mode')->nullable()->default('2');
            $table->string('status')->nullable()->default('PENDING');
            $table->string('credit_account_number')->nullable();
            $table->string('debit_account_number')->nullable();
            $table->string('payment_link')->nullable();
            $table->string('payment_link_id')->nullable();
            $table->timestamp('payment_link_generated_at')->nullable();
            $table->json('payment_link_items')->nullable();
            $table->index(['client_number']);
            $table->index(['service_id']);
            $table->index(['due_date']);
            $table->index(['created_at']);
            $table->foreign('service_id')->references('id')->on('services')->onDelete('cascade');
            $table->foreign('created_by')->references('id')->on('users')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('bills');
    }
};