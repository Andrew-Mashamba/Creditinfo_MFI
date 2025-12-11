<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for roles table
 * 
 * Combined from these migrations:
 * - 2024_03_15_000000_create_roles_table.php
 * - 2024_03_15_create_permissions_table.php
 * - 2025_05_09_130356_add_hierarchy_columns_to_roles.php
 * - 2025_06_05_162236_add_level_to_roles_table.php
 * - 2025_07_03_090455_add_guard_name_to_roles_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('roles', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('department_id')->nullable();
            $table->text('description')->nullable();
            $table->integer('level')->default(0);
            $table->boolean('is_system_role')->default(false);
            $table->softDeletes();
            $table->boolean('permission_inheritance_enabled')->default(true);
            $table->boolean('department_specific')->default(false);
            $table->json('conditions')->nullable();
            $table->bigInteger('parent_role_id')->nullable();
            $table->string('path')->nullable();
            $table->string('guard_name')->default('web');
            $table->index(['department_id']);
            $table->index(['is_system_role']);
            $table->index(['level']);
            $table->index(['name']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('parent_role_id')->references('id')->on('roles');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('roles');
    }
};