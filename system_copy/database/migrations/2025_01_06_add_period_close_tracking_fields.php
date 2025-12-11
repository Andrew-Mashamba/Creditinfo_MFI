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
        if (Schema::hasTable('budget_managements')) {
            Schema::table('budget_managements', function (Blueprint $table) {
                // Add period close tracking fields
                if (!Schema::hasColumn('budget_managements', 'last_period_close')) {
                    $table->timestamp('last_period_close')->nullable()->after('last_calculated_at');
                }
                if (!Schema::hasColumn('budget_managements', 'last_closed_period')) {
                    $table->string('last_closed_period', 20)->nullable()->after('last_period_close');
                }
            });
        }
        
        // Update budget_versions table to support new version types - only if table exists
        if (Schema::hasTable('budget_versions')) {
            Schema::table('budget_versions', function (Blueprint $table) {
                if (!Schema::hasColumn('budget_versions', 'version_type')) {
                    $table->string('version_type', 50)->default('REVISED')->after('version_name');
                }
                
                // Update the enum values if using MySQL
                if (config('database.default') === 'mysql') {
                    DB::statement("ALTER TABLE budget_versions MODIFY COLUMN version_type 
                        ENUM('ORIGINAL', 'REVISED', 'TRANSFER', 'MILESTONE', 'MONTHLY_CLOSE', 'QUARTERLY_CLOSE', 'CANCELLED', 'APPROVED_EDIT') 
                        DEFAULT 'REVISED'");
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_managements', function (Blueprint $table) {
            $table->dropColumn(['last_period_close', 'last_closed_period']);
        });
        
        Schema::table('budget_versions', function (Blueprint $table) {
            // Revert to original enum values if needed
            if (config('database.default') === 'mysql') {
                DB::statement("ALTER TABLE budget_versions MODIFY COLUMN version_type 
                    ENUM('ORIGINAL', 'REVISED') DEFAULT 'REVISED'");
            }
        });
    }
};