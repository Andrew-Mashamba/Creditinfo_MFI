<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('daily_activity_status', function (Blueprint $table) {
            // Drop the existing unique constraint on activity_key alone
            $table->dropUnique(['activity_key']);
            
            // Add a composite unique constraint on activity_key and process_date
            $table->unique(['activity_key', 'process_date'], 'daily_activity_status_activity_date_unique');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('daily_activity_status', function (Blueprint $table) {
            // Drop the composite unique constraint
            $table->dropUnique('daily_activity_status_activity_date_unique');
            
            // Restore the original unique constraint on activity_key alone
            $table->unique('activity_key');
        });
    }
};