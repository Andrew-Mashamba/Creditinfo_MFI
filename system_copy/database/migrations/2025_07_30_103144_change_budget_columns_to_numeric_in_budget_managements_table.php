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
        // First, let's check if there's any data and convert it safely
        $records = DB::table('budget_managements')->get();
        
        // Convert string values to numeric before changing column types
        foreach ($records as $record) {
            $updates = [];
            
            // Convert revenue
            if (isset($record->revenue) && is_numeric($record->revenue)) {
                $updates['revenue'] = (float) $record->revenue;
            } elseif (isset($record->revenue)) {
                $updates['revenue'] = 0;
            }
            
            // Convert expenditure
            if (isset($record->expenditure) && is_numeric($record->expenditure)) {
                $updates['expenditure'] = (float) $record->expenditure;
            } elseif (isset($record->expenditure)) {
                $updates['expenditure'] = 0;
            }
            
            // Convert capital_expenditure
            if (isset($record->capital_expenditure) && is_numeric($record->capital_expenditure)) {
                $updates['capital_expenditure'] = (float) $record->capital_expenditure;
            } elseif (isset($record->capital_expenditure)) {
                $updates['capital_expenditure'] = 0;
            }
            
            // Convert spent_amount
            if (isset($record->spent_amount) && is_numeric($record->spent_amount)) {
                $updates['spent_amount'] = (float) $record->spent_amount;
            } elseif (isset($record->spent_amount)) {
                $updates['spent_amount'] = 0;
            }
            
            if (!empty($updates)) {
                DB::table('budget_managements')
                    ->where('id', $record->id)
                    ->update($updates);
            }
        }
        
        // Now change the column types using raw SQL for proper casting
        DB::statement('ALTER TABLE budget_managements ALTER COLUMN revenue TYPE NUMERIC(15,2) USING revenue::NUMERIC(15,2)');
        DB::statement('ALTER TABLE budget_managements ALTER COLUMN expenditure TYPE NUMERIC(15,2) USING expenditure::NUMERIC(15,2)');
        DB::statement('ALTER TABLE budget_managements ALTER COLUMN capital_expenditure TYPE NUMERIC(15,2) USING capital_expenditure::NUMERIC(15,2)');
        DB::statement('ALTER TABLE budget_managements ALTER COLUMN spent_amount TYPE NUMERIC(15,2) USING spent_amount::NUMERIC(15,2)');
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('budget_managements', function (Blueprint $table) {
            $table->string('revenue')->change();
            $table->string('expenditure')->change();
            $table->string('capital_expenditure')->change();
            $table->string('spent_amount')->change();
        });
    }
};
