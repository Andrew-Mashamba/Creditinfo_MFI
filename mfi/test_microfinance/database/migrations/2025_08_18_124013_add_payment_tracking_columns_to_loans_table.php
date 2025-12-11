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
            if (!Schema::hasColumn('loans', 'total_principal_paid')) {
                $table->decimal('total_principal_paid', 20, 2)->default(0)->after('total_principal');
            }
            if (!Schema::hasColumn('loans', 'total_interest_paid')) {
                $table->decimal('total_interest_paid', 20, 2)->default(0)->after('total_interest');
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
            $table->dropColumn(['total_principal_paid', 'total_interest_paid']);
        });
    }
};