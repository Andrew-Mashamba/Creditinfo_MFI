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
        Schema::table('institutions', function (Blueprint $table) {
            $table->decimal('writeoff_board_approval_threshold', 15, 2)->default(1000000)->after('insurance_percentage');
            $table->decimal('writeoff_manager_approval_threshold', 15, 2)->default(500000)->after('writeoff_board_approval_threshold');
            $table->integer('writeoff_minimum_collection_efforts')->default(3)->after('writeoff_manager_approval_threshold');
            $table->integer('writeoff_recovery_tracking_period')->default(36)->after('writeoff_minimum_collection_efforts');
        });
        
        // Update existing institutions with default values
        DB::table('institutions')->update([
            'writeoff_board_approval_threshold' => 1000000,
            'writeoff_manager_approval_threshold' => 500000,
            'writeoff_minimum_collection_efforts' => 3,
            'writeoff_recovery_tracking_period' => 36
        ]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('institutions', function (Blueprint $table) {
            $table->dropColumn([
                'writeoff_board_approval_threshold',
                'writeoff_manager_approval_threshold',
                'writeoff_minimum_collection_efforts',
                'writeoff_recovery_tracking_period'
            ]);
        });
    }
};