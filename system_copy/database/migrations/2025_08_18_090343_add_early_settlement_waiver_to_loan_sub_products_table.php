<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('loan_sub_products', function (Blueprint $table) {
            // Add early settlement waiver percentage field
            $table->decimal('early_settlement_waiver', 5, 2)->nullable()->default(0)
                  ->comment('Percentage of interest to waive for early settlement (0-100)');
            
            // Add penalty max cap if it doesn't exist
            if (!Schema::hasColumn('loan_sub_products', 'penalty_max_cap')) {
                $table->decimal('penalty_max_cap', 10, 2)->nullable()
                      ->comment('Maximum penalty amount cap');
            }
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_sub_products', function (Blueprint $table) {
            $table->dropColumn('early_settlement_waiver');
            
            if (Schema::hasColumn('loan_sub_products', 'penalty_max_cap')) {
                $table->dropColumn('penalty_max_cap');
            }
        });
    }
};
