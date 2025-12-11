<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('orders', function (Blueprint $table) {
            $table->id();
            $table->string('team_id', 255)->nullable();
            $table->string('user_id', 255)->nullable();
            $table->string('order_number', 255)->nullable();
            $table->string('order_status', 255)->nullable();
            $table->string('order_failed_transaction', 255)->nullable();
            $table->smallInteger('completed')->default(0)->notNullable();
            $table->string('source_account', 255)->nullable();
            $table->string('amountOfTransactions', 255)->nullable();
            $table->string('typeOfTransfer', 255)->nullable();
            $table->string('first_authorizer_id', 255)->nullable();
            $table->string('first_authorizer_action', 255)->notNullable();
            $table->text('first_authorizer_comments')->nullable();
            $table->string('second_authorizer_id', 255)->nullable();
            $table->string('second_authorizer_action', 255)->notNullable();
            $table->text('second_authorizer_comments')->nullable();
            $table->string('third_authorizer_id', 255)->nullable();
            $table->string('third_authorizer_action', 255)->notNullable();
            $table->text('third_authorizer_comments')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('orders');
    }
}; 