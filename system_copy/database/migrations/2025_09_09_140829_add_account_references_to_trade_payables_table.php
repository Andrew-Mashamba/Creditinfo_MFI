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
            // Add account reference fields
            $table->string('parent_account_number')->nullable()->after('account_number')
                ->comment('Parent liability account where payable was created');
            $table->unsignedBigInteger('other_account_id')->nullable()->after('parent_account_number')
                ->comment('Expense/Inventory account for double-entry');
            $table->unsignedBigInteger('payable_account_id')->nullable()->after('other_account_id')
                ->comment('The created payable account ID');
            
            // Add indexes for better query performance
            $table->index('parent_account_number');
            $table->index('other_account_id');
            $table->index('payable_account_id');
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
            $table->dropIndex(['parent_account_number']);
            $table->dropIndex(['other_account_id']);
            $table->dropIndex(['payable_account_id']);
            
            $table->dropColumn('parent_account_number');
            $table->dropColumn('other_account_id');
            $table->dropColumn('payable_account_id');
        });
    }
};