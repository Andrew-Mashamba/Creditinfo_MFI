<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for notifications table
 * 
 * Combined from these migrations:
 * - 2024_03_21_000004_create_notifications_table.php
 * - 2025_03_21_add_indexes_to_shares_tables.php
 * - 2025_06_10_032947_update_notifications_table_for_shares.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('notifications', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('member_id');
            $table->string('type');
            $table->string('title');
            $table->text('message');
            $table->string('status')->default('unread');
            $table->string('action_url')->nullable();
            $table->string('action_text')->nullable();
            $table->timestamp('read_at')->nullable();
            $table->index(['member_id']);
            $table->index(['type']);
            $table->index(['status']);
            $table->index(['created_at']);
            $table->index(['member_id', 'status']);
            $table->timestamps();
            $table->index(['type', 'status']);
            $table->foreign('member_id')->references('id')->on('clients')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('notifications');
    }
};