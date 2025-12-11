<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('movable_property_types', function (Blueprint $table) {
            $table->id();
            $table->integer('main_type_id')->nullable();
            $table->string('type_name', 255)->notNullable();
            $table->boolean('is_landed')->default(false)->notNullable();
            $table->boolean('requires_insurance')->default(false)->notNullable();
            $table->boolean('requires_valuation')->default(false)->notNullable();
            $table->boolean('is_matrimonial')->default(false)->notNullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('movable_property_types');
    }
}; 