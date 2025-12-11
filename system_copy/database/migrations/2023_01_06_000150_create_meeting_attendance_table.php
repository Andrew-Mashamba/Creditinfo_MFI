<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

/**
 * Consolidated migration for meeting_attendance table
 * 
 * Combined from these migrations:
 * - 2024_07_04_100030_create_meeting_attendance_table.php
 * - 2024_07_05_000000_add_stipend_to_meeting_attendance.php
 */
return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('meeting_attendance', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('meeting_id');
            $table->bigInteger('leader_id');
            $table->string('status')->default('present');
            $table->text('notes')->nullable();
            $table->boolean('stipend_paid')->default(false);
            $table->decimal('stipend_amount', 12, 2)->nullable();
            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('leader_id')->references('id')->on('leaderships')->onDelete('cascade');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('meeting_attendance');
    }
};