<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for permissions table
 * 
 * Combined from these migrations:
 * - 2024_03_15_create_permissions_table.php
 * - 2025_07_03_084716_add_guard_name_to_permissions_table.php
 * - 2025_07_03_090240_add_slug_to_permissions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('permissions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->string('slug');
            $table->string('description')->nullable();
            $table->string('module')->nullable();
            $table->string('action');
            $table->string('resource_type')->nullable();
            $table->string('resource_id')->nullable();
            $table->json('conditions')->nullable();
            $table->boolean('is_system')->default(false);
            $table->string('guard_name')->default('web');
            $table->timestamps();
            $table->index(['name']);
            $table->index(['slug']);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('permissions');
    }
};