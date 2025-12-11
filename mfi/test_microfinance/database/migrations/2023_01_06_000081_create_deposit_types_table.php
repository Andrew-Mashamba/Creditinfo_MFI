<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('deposit_types', function (Blueprint $table) {
            $table->id();
            $table->string('type');
            $table->text('summary')->nullable();
            $table->boolean('status')->default(true);
            $table->bigInteger('institution_id')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('deposit_types');
    }
}; 