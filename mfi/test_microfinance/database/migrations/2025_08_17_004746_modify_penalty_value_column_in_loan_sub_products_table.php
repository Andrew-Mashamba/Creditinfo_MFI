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
        Schema::table('loan_sub_products', function (Blueprint $table) {
            // Change penalty_value from string to decimal for percentage storage
            // Use USING clause for PostgreSQL compatibility
            DB::statement('ALTER TABLE loan_sub_products ALTER COLUMN penalty_value TYPE DECIMAL(5,2) USING CASE WHEN penalty_value ~ \'^[0-9]+\.?[0-9]*$\' AND penalty_value::DECIMAL <= 999.99 THEN penalty_value::DECIMAL(5,2) ELSE NULL END');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loan_sub_products', function (Blueprint $table) {
            // Revert back to string
            $table->string('penalty_value', 250)->nullable()->change();
        });
    }
};
