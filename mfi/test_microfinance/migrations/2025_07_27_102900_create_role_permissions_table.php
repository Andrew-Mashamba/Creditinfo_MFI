<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for role_permissions table
 * 
 * Combined from these migrations:
 * - 2024_03_15_create_permissions_table.php
 * - 2024_06_05_000001_add_constraints_to_role_permissions_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('role_permissions', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('role_id');
            $table->bigInteger('permission_id');
            $table->bigInteger('department_id')->nullable();
            $table->json('conditions')->nullable();
            $table->boolean('is_inherited')->default(false);
            $table->json('constraints')->nullable();
            $table->index(['role_id', 'permission_id', 'department_id']);
            $table->foreign('role_id')->references('id')->on('roles')->onDelete('cascade');
            $table->foreign('permission_id')->references('id')->on('permissions')->onDelete('cascade');
            $table->foreign('department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('role_permissions');
    }
};