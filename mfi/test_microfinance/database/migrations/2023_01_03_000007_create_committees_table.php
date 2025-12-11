<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for committees table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_committees_table.php
 * - 2025_05_09_130357_add_hierarchy_columns_to_committees.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('committees', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->bigInteger('department_id');
            $table->string('loan_category')->nullable();
            $table->integer('min_approvals_required')->default(1);
            $table->integer('approval_order')->default(0);
            $table->string('type')->default('LOAN');
            $table->integer('level')->default(0);
            $table->softDeletes();
            $table->bigInteger('parent_committee_id')->nullable();
            $table->string('path')->nullable();
            $table->timestamps();
            $table->index(['department_id']);
            $table->index(['loan_category']);
            $table->index(['status']);
            $table->index(['type']);
            $table->index(['level']);
            $table->index(['name']);
            $table->foreign('department_id')->references('id')->on('departments')->onDelete('cascade');
            $table->foreign('parent_committee_id')->references('id')->on('committees');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('committees');
    }
};