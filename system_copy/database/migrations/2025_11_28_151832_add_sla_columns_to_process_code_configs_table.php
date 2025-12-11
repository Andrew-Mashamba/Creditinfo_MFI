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
        Schema::table('process_code_configs', function (Blueprint $table) {
            // SLA Configuration (in hours)
            $table->integer('sla_hours_first_check')->nullable()->default(4)->after('is_active');
            $table->integer('sla_hours_second_check')->nullable()->default(4)->after('sla_hours_first_check');
            $table->integer('sla_hours_approval')->nullable()->default(8)->after('sla_hours_second_check');

            // Escalation settings
            $table->string('sla_escalation_email')->nullable()->after('sla_hours_approval');
            $table->boolean('sla_enabled')->default(true)->after('sla_escalation_email');

            // Warning threshold (percentage of SLA time before warning)
            $table->integer('sla_warning_threshold')->nullable()->default(75)->after('sla_enabled');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('process_code_configs', function (Blueprint $table) {
            $table->dropColumn([
                'sla_hours_first_check',
                'sla_hours_second_check',
                'sla_hours_approval',
                'sla_escalation_email',
                'sla_enabled',
                'sla_warning_threshold'
            ]);
        });
    }
};
