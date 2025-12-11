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
        Schema::table('payable_payments', function (Blueprint $table) {
            $table->string('transfer_type', 20)->nullable()->after('notes')
                ->comment('internal, external, or standard');
            $table->string('transfer_reference')->nullable()->after('transfer_type')
                ->comment('Reference from transfer service');
            $table->string('nbc_reference')->nullable()->after('transfer_reference')
                ->comment('NBC bank reference number');
            $table->string('routing_system', 10)->nullable()->after('nbc_reference')
                ->comment('TIPS or TISS for external transfers');
            
            // Add indexes for searching
            $table->index('transfer_type');
            $table->index('transfer_reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('payable_payments', function (Blueprint $table) {
            $table->dropIndex(['transfer_type']);
            $table->dropIndex(['transfer_reference']);
            
            $table->dropColumn([
                'transfer_type',
                'transfer_reference', 
                'nbc_reference',
                'routing_system'
            ]);
        });
    }
};
