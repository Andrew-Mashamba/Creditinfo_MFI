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
            // Add fields to identify system/permanent payables
            if (!Schema::hasColumn('trade_payables', 'is_system')) {
                $table->boolean('is_system')->default(false)->after('is_recurring')
                    ->comment('System-generated payables that cannot be deleted');
            }
            if (!Schema::hasColumn('trade_payables', 'is_enabled')) {
                $table->boolean('is_enabled')->default(true)->after('is_system')
                    ->comment('Whether the payable is enabled or disabled');
            }
            if (!Schema::hasColumn('trade_payables', 'system_code')) {
                $table->string('system_code')->nullable()->after('service_type')
                    ->comment('System service code (SMS, EMAIL, CTRL, etc.)');
            }
            
            // Add index for system payables
            $table->index(['is_system'], 'trade_payables_is_system_index');
            $table->index(['is_enabled'], 'trade_payables_is_enabled_index');
            $table->index(['system_code'], 'trade_payables_system_code_index');
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
            // Drop indexes first
            $table->dropIndex('trade_payables_is_system_index');
            $table->dropIndex('trade_payables_is_enabled_index');
            $table->dropIndex('trade_payables_system_code_index');
            
            // Drop columns
            $table->dropColumn([
                'is_system',
                'is_enabled',
                'system_code'
            ]);
        });
    }
};