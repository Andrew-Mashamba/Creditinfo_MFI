<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for ai_interactions table
 * 
 * Combined from these migrations:
 * - 2024_01_15_000000_create_ai_interactions_table.php
 * - 2025_07_06_102444_add_user_id_to_ai_interactions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('ai_interactions', function (Blueprint $table) {
            $table->id();
            $table->string('session_id');
            $table->text('query');
            $table->text('response');
            $table->json('context')->nullable();
            $table->json('metadata')->nullable();
            $table->bigInteger('user_id')->nullable();
            $table->index(['session_id', 'created_at']);
            $table->index(['created_at']);
            $table->index(['session_id']);
            $table->timestamps();
            $table->index(['user_id']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('ai_interactions');
    }
};