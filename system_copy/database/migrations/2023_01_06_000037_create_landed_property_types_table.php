<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('landed_property_types', function (Blueprint $table) {
            $table->id();
            $table->integer('main_type_id')->nullable();
            $table->string('type_name', 255)->notNullable();
            $table->boolean('is_landed')->default(true);
            $table->boolean('requires_insurance')->default(false);
            $table->boolean('requires_valuation')->default(false);
            $table->boolean('is_matrimonial')->default(false);
        });
    }

    public function down()
    {
        Schema::dropIfExists('landed_property_types');
    }
}; 