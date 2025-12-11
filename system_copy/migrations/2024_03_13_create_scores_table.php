<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('scores', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('client_id')->notNullable();
            $table->timestamp('date')->notNullable();
            $table->string('grade')->notNullable();
            $table->integer('score')->notNullable();
            $table->string('trend')->notNullable();
            $table->jsonb('reasons')->nullable();
            $table->string('probability_of_default')->notNullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('scores');
    }
}; 