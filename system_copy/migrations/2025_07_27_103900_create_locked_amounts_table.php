<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for locked_amounts table
 * 
 * Combined from these migrations:
 * - 2025_06_28_142212_create_locked_amounts_table.php
 * - 2025_06_28_153000_update_locked_amounts_status_enum.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('locked_amounts', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('account_id');
            $table->decimal('amount', 15, 2);
            $table->string('service_type');
            $table->bigInteger('service_id');
            $table->string('reason');
            $table->string('status')->default('ACTIVE');
            $table->text('description')->nullable();
            $table->timestamp('locked_at')->default(CURRENT_TIMESTAMP);
            $table->timestamp('released_at')->nullable();
            $table->timestamp('expires_at')->nullable();
            $table->bigInteger('locked_by')->nullable();
            $table->bigInteger('released_by')->nullable();
            $table->index(['account_id', 'status']);
            $table->index(['service_type', 'service_id']);
            $table->index(['status', 'locked_at']);
            $table->index(['expires_at']);
            $table->foreign('account_id')->references('id')->on('accounts')->onDelete('cascade');
            $table->foreign('locked_by')->references('id')->on('users');
            $table->foreign('released_by')->references('id')->on('users');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('locked_amounts');
    }
};