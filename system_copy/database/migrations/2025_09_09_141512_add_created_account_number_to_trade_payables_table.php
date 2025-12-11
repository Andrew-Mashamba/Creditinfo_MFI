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
        Schema::table('trade_payables', function (Blueprint $table) {
            // Add field to store the created payable account number
            $table->string('created_payable_account_number')->nullable()->after('payable_account_id')
                ->comment('The account number of the created payable account');
            
            // Add index for better query performance
            $table->index('created_payable_account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_payables', function (Blueprint $table) {
            $table->dropIndex(['created_payable_account_number']);
            $table->dropColumn('created_payable_account_number');
        });
    }
};