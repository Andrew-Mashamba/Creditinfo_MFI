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
        // Update existing loans that don't have approval_stage set
        // Set them to appropriate stages based on their status
        
        // Set default to Inputter for loans with NULL approval_stage
        DB::table('loans')
            ->whereNull('approval_stage')
            ->orWhere('approval_stage', '')
            ->update(['approval_stage' => 'Inputter']);
            
        // For approved loans, set to FINANCE
        DB::table('loans')
            ->where('status', 'APPROVED')
            ->update(['approval_stage' => 'FINANCE']);
            
        // For rejected loans, keep at their current stage (already updated to Inputter above)
        // For pending approval, they might be at different stages, but Inputter is a safe default
        
        // For loans with PENDING-EXCEPTIONS status, set to Exception stage
        DB::table('loans')
            ->whereIn('status', ['PENDING-EXCEPTIONS', 'PENDING-WITH-EXCEPTIONS', 'PENDING_EXCEPTION_APPROVAL'])
            ->update(['approval_stage' => 'Exception']);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // We can't really reverse this data update
        // But we could set all back to NULL if needed
        // DB::table('loans')->update(['approval_stage' => null]);
    }
};