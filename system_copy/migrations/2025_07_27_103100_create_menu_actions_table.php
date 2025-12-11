<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for menu_actions table
 * 
 * Combined from these migrations:
 * - 2024_03_19_000002_create_menu_actions_table.php
 * - 2025_07_27_090100_update_menu_actions_table_structure.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('menu_actions', function (Blueprint $table) {
            $table->id();
            $table->string('name');
            $table->bigInteger('menu_id')->nullable();
            $table->text('description')->nullable();
            $table->string('slug')->nullable();
            $table->foreign('menu_id')->references('id')->on('menus')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('menu_actions');
    }
};