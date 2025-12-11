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
        Schema::table('loans', function (Blueprint $table) {
            if (!Schema::hasColumn('loans', 'loan_classification')) {
                $table->string('loan_classification', 20)->default('PERFORMING')->after('loan_status');
            }
            if (!Schema::hasColumn('loans', 'total_arrears')) {
                $table->decimal('total_arrears', 20, 2)->default(0)->after('balance');
            }
            if (!Schema::hasColumn('loans', 'days_in_arrears')) {
                $table->integer('days_in_arrears')->default(0)->after('total_arrears');
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
        Schema::table('loans', function (Blueprint $table) {
            $table->dropColumn(['loan_classification', 'total_arrears', 'days_in_arrears']);
        });
    }
};
