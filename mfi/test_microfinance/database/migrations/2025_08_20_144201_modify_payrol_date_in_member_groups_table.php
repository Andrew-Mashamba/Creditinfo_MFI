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
        Schema::table('member_groups', function (Blueprint $table) {
            $table->dropColumn('payrol_date');
        });
        
        Schema::table('member_groups', function (Blueprint $table) {
            $table->integer('payrol_date')->nullable()->comment('Day of month (1-31) for payroll');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('member_groups', function (Blueprint $table) {
            $table->date('payrol_date')->nullable()->change();
        });
    }
};