<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddLastPaymentDateToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            if (!Schema::hasColumn('trade_receivables', 'last_payment_date')) {
                $table->date('last_payment_date')->nullable()
                    ->after('paid_amount')
                    ->comment('Date of the last payment received');
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
        Schema::table('trade_receivables', function (Blueprint $table) {
            if (Schema::hasColumn('trade_receivables', 'last_payment_date')) {
                $table->dropColumn('last_payment_date');
            }
        });
    }
}