<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cash_flow_configurations', function (Blueprint $table) {
            $table->id();
            $table->string('section');
            $table->unsignedBigInteger('account_id');
            $table->string('operation');
            $table->timestampTz('created_at')->default(now());
            $table->timestampTz('updated_at')->default(now());

            $table->foreign('account_id')
                  ->references('id')
                  ->on('accounts')
                  ->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cash_flow_configurations');
    }
}; 