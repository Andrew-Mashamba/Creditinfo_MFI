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
        // First, let's check if there are any non-numeric values in the balance column
        $nonNumericBalances = DB::table('accounts')
            ->whereRaw("balance !~ '^[0-9]+\.?[0-9]*$'")
            ->whereNotNull('balance')
            ->where('balance', '!=', '')
            ->count();

        if ($nonNumericBalances > 0) {
            // If there are non-numeric values, convert them to 0
            DB::table('accounts')
                ->whereRaw("balance !~ '^[0-9]+\.?[0-9]*$'")
                ->orWhereNull('balance')
                ->orWhere('balance', '')
                ->update(['balance' => '0']);
        }

        // Drop the default value first
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance DROP DEFAULT');
        
        // Change the column type to decimal
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance TYPE DECIMAL(15,2) USING balance::DECIMAL(15,2)');
        
        // Set new default value
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance SET DEFAULT 0');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        // Drop the default value
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance DROP DEFAULT');
        
        // Change back to character varying
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance TYPE VARCHAR(255)');
        
        // Set old default value
        DB::statement('ALTER TABLE accounts ALTER COLUMN balance SET DEFAULT \'0\'');
    }
};
