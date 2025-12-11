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
        Schema::table('loans_schedules', function (Blueprint $table) {
            if (!Schema::hasColumn('loans_schedules', 'status')) {
                $table->string('status', 255)->nullable()->default(null)->after('completion_status');
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
        Schema::table('loans_schedules', function (Blueprint $table) {
            if (Schema::hasColumn('loans_schedules', 'status')) {
                $table->dropColumn('status');
            }
        });
    }
};
