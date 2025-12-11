<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for approval_comments table
 * 
 * Combined from these migrations:
 * - 2024_03_20_000007_recreate_approvals_table_with_all_fields.php
 * - 2025_06_03_040000_recreate_approvals_table_with_nullable_institution.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('approval_comments', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('approval_id');
            $table->text('comment');
            $table->foreign('approval_id')->references('id')->on('approvals')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('approval_comments');
    }
};