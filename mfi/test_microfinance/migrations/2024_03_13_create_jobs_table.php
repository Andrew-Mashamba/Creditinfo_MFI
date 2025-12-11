<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('jobs', function (Blueprint $table) {
            $table->id();
            $table->string('queue', 255)->nullable();
            $table->text('payload')->nullable();
            $table->smallInteger('attempts')->nullable();
            $table->integer('reserved_at')->nullable();
            $table->integer('available_at')->nullable();
            $table->integer('created_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('jobs');
    }
}; 