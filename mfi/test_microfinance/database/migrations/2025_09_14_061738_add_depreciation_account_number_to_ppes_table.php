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
        Schema::table('ppes', function (Blueprint $table) {
            $table->string('depreciation_account_number')->nullable()->after('account_number');
            $table->index('depreciation_account_number');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('ppes', function (Blueprint $table) {
            $table->dropIndex(['depreciation_account_number']);
            $table->dropColumn('depreciation_account_number');
        });
    }
};
