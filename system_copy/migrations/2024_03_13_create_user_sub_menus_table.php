<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('user_sub_menus', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('menu_id')->nullable();
            $table->integer('sub_menu_id')->nullable();
            $table->string('permission', 50)->nullable();
            $table->integer('updated')->nullable();
            $table->integer('previous')->nullable();
            $table->string('status', 50)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('user_sub_menus');
    }
}; 