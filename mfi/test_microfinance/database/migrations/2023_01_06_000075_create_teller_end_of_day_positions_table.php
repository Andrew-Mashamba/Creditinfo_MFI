<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('teller_end_of_day_positions', function (Blueprint $table) {
            $table->id();
            $table->string('employee_id', 100)->notNullable();
            $table->string('institution_id', 20)->notNullable();
            $table->string('branch_id', 20)->notNullable();
            $table->string('til_number', 100)->notNullable();
            $table->string('til_account', 100)->notNullable();
            $table->string('til_balance', 100)->notNullable();
            $table->string('tiller_cash_at_hand', 100)->notNullable();
            $table->date('business_date')->nullable();
            $table->string('message', 250)->nullable();
            $table->string('status', 10)->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('teller_end_of_day_positions');
    }
}; 