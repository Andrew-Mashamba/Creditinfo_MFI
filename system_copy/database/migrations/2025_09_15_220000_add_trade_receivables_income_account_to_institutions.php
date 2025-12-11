<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddTradeReceivablesIncomeAccountToInstitutions extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('institutions', function (Blueprint $table) {
            if (!Schema::hasColumn('institutions', 'trade_receivables_income_account')) {
                $table->string('trade_receivables_income_account')->nullable()
                    ->default('0101400045004520')
                    ->after('trade_receivables_account')
                    ->comment('Income account for trade receivables payments');
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
        Schema::table('institutions', function (Blueprint $table) {
            if (Schema::hasColumn('institutions', 'trade_receivables_income_account')) {
                $table->dropColumn('trade_receivables_income_account');
            }
        });
    }
}