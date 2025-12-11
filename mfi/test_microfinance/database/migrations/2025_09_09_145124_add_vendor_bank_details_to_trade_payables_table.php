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
            // Add vendor bank details fields
            $table->string('vendor_bank_name')->nullable()->after('vendor_name')
                ->comment('Vendor bank name for payment');
            $table->string('vendor_bank_account_number')->nullable()->after('vendor_bank_name')
                ->comment('Vendor bank account number');
            $table->string('vendor_bank_branch')->nullable()->after('vendor_bank_account_number')
                ->comment('Vendor bank branch');
            $table->string('vendor_swift_code')->nullable()->after('vendor_bank_branch')
                ->comment('SWIFT code for international transfers');
            
            // Add indexes for better query performance
            $table->index('vendor_bank_account_number');
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
            $table->dropIndex(['vendor_bank_account_number']);
            
            $table->dropColumn('vendor_bank_name');
            $table->dropColumn('vendor_bank_account_number');
            $table->dropColumn('vendor_bank_branch');
            $table->dropColumn('vendor_swift_code');
        });
    }
};