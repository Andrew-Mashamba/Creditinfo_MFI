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
        // Fix the sequence for general_ledger table
        $maxId = DB::table('general_ledger')->max('id') ?? 1;
        DB::select("SELECT setval('general_ledger_id_seq', {$maxId})");
        
        // Add indexes to improve performance (without unique constraint due to existing duplicates)
        Schema::table('general_ledger', function (Blueprint $table) {
            // Add index on record_on_account_number for better query performance
            $table->index('record_on_account_number', 'general_ledger_account_number_index');
            
            // Add index on created_at for better query performance
            $table->index('created_at', 'general_ledger_created_at_index');
            
            // Add index on reference_number for better query performance (not unique due to existing duplicates)
            $table->index('reference_number', 'general_ledger_reference_number_index');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('general_ledger', function (Blueprint $table) {
            $table->dropIndex('general_ledger_account_number_index');
            $table->dropIndex('general_ledger_created_at_index');
            $table->dropIndex('general_ledger_reference_number_index');
        });
    }
};
