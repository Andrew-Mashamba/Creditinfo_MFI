<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('inventories', function (Blueprint $table) {
            $table->id();
            $table->string('item_name', 255)->notNullable();
            $table->double('item_amount')->notNullable();
            $table->date('item_expiration_date')->notNullable();
            $table->string('status', 11)->notNullable();
            $table->string('item_description', 255)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('inventories');
    }
}; 