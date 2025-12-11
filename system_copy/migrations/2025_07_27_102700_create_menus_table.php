<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for menus table
 * 
 * Combined from these migrations:
 * - 2024_03_13_create_menus_table.php
 * - 2025_07_27_090000_update_menus_table_structure.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menus', function (Blueprint $table) {
            $table->id();
            $table->integer('system_id')->nullable();
            $table->string('menu_name')->nullable();
            $table->string('menu_description')->nullable();
            $table->string('menu_title')->nullable();
            $table->string('status')->default('PENDING');
            $table->integer('menu_number')->nullable();
            $table->string('name')->nullable();
            $table->string('route')->nullable();
            $table->string('icon')->nullable();
            $table->bigInteger('parent_id')->nullable();
            $table->integer('order')->default(0);
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menus');
    }
};