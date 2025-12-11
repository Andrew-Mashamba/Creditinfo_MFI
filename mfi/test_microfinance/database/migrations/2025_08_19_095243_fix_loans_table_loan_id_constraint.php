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
        Schema::table('loans', function (Blueprint $table) {
            // Ensure loan_id column can handle the generated loan IDs (15 characters)
            // Change from VARCHAR(20) to VARCHAR(50) to be safe
            $table->string('loan_id', 50)->nullable()->change();
            
            // Also ensure loan_account_number can handle the generated account numbers
            $table->string('loan_account_number', 50)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('loans', function (Blueprint $table) {
            // Revert back to original constraints
            $table->string('loan_id', 20)->nullable()->change();
            $table->string('loan_account_number', 20)->nullable()->change();
        });
    }
};
