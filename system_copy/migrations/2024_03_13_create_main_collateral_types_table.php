<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('main_collateral_types', function (Blueprint $table) {
            $table->id();
            $table->string('main_type_name', 255)->notNullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('main_collateral_types');
    }
}; 