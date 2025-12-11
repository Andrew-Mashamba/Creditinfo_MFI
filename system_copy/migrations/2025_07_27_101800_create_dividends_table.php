<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for dividends table
 * 
 * Combined from these migrations:
 * - 2024_03_19_create_dividends_table.php
 * - 2024_03_21_000001_create_dividends_table.php
 * - 2024_03_22_create_dividends_table.php
 * - 2025_03_21_add_indexes_to_shares_tables.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('dividends', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id');
            $table->integer('year');
            $table->decimal('rate', 5, 2);
            $table->decimal('amount', 15, 2);
            $table->timestamp('paid_at')->nullable();
            $table->string('payment_mode')->default('bank');
            $table->string('status')->default('pending');
            $table->text('narration')->nullable();
            $table->index(['year']);
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['year', 'status']);
            $table->foreign('member_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('dividends');
    }
};