<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('budget_approvers', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id');
            $table->string('user_name');
            $table->string('status');
            $table->integer('budget_id');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('budget_approvers');
    }
}; 