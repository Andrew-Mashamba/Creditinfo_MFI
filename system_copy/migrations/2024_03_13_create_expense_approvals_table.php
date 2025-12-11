<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('expense_approvals', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->notNullable();
            $table->string('user_name', 255)->notNullable();
            $table->string('status', 255)->notNullable();
            $table->integer('expense_id')->notNullable();
            $table->string('approval_level', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('expense_approvals');
    }
}; 