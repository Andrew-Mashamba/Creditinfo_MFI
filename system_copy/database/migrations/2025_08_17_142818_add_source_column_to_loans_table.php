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
            // Add source column to track where loan was created from (OFFICE, MOBILE, WEB, API)
            if (!Schema::hasColumn('loans', 'source')) {
                $table->string('source', 50)->nullable()->default('OFFICE')->after('arrears_in_amount');
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
            $table->dropColumn('source');
        });
    }
};