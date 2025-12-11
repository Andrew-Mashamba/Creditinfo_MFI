<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        // Drop the existing unique constraint
        Schema::table('depreciation_postings', function (Blueprint $table) {
            $table->dropUnique(['asset_id', 'period_year', 'period_month', 'period_type']);
        });
        
        // Create a partial unique index that only applies to 'posted' status
        // This allows multiple reversed entries but only one posted entry per period
        DB::statement("CREATE UNIQUE INDEX depreciation_postings_unique_posted 
                       ON depreciation_postings (asset_id, period_year, period_month, period_type) 
                       WHERE status = 'posted'");
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the partial unique index
        DB::statement("DROP INDEX IF EXISTS depreciation_postings_unique_posted");
        
        // Recreate the original unique constraint
        Schema::table('depreciation_postings', function (Blueprint $table) {
            $table->unique(['asset_id', 'period_year', 'period_month', 'period_type']);
        });
    }
};