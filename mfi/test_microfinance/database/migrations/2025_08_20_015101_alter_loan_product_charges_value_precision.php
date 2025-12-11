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
        Schema::table('loan_product_charges', function (Blueprint $table) {
            // Change value column from decimal(10,2) to decimal(10,4) 
            // to allow for more precise percentage values like 0.125
            $table->decimal('value', 10, 4)->nullable()->change();
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('loan_product_charges', function (Blueprint $table) {
            // Revert back to decimal(10,2)
            $table->decimal('value', 10, 2)->nullable()->change();
        });
    }
};