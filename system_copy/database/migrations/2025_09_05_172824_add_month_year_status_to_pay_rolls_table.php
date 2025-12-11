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
        Schema::table('pay_rolls', function (Blueprint $table) {
            $table->integer('month')->nullable()->after('payment_date');
            $table->integer('year')->nullable()->after('month');
            $table->string('status')->default('pending')->after('year');
            
            // Add indexes for performance
            $table->index(['month', 'year']);
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('pay_rolls', function (Blueprint $table) {
            $table->dropIndex(['month', 'year']);
            $table->dropIndex(['status']);
            $table->dropColumn(['month', 'year', 'status']);
        });
    }
};