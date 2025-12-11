<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('system_id')->nullable();
            $table->integer('menu_id')->nullable();
            $table->string('sub_menu_name', 100)->nullable();
            $table->string('user_action', 100)->nullable();
            $table->string('status', 40)->default('PENDING');
            $table->integer('position')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_menus');
    }
}; 