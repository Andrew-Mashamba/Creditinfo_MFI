<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for departments table
 * 
 * Combined from these migrations:
 * - 2024_03_13_100002_create_departments_table.php
 * - 2025_05_09_130355_add_hierarchy_columns_to_departments.php
 * - 2025_06_17_075658_add_branch_id_to_departments_table.php
 * - 2025_07_04_130515_add_dashboard_type_to_departments_table.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('departments', function (Blueprint $table) {
            $table->id();
            $table->string('department_name');
            $table->string('department_code');
            $table->bigInteger('parent_department_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->integer('level')->default(0);
            $table->string('path')->nullable();
            $table->softDeletes();
            $table->string('branch_id')->nullable();
            $table->integer('dashboard_type')->nullable();
            $table->index(['department_code']);
            $table->index(['parent_department_id']);
            $table->index(['path']);
            $table->index(['level']);
            $table->index(['department_code']);
            $table->foreign('parent_department_id')->references('id')->on('departments');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('departments');
    }
};