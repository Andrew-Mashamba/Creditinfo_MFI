<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meetings', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('committee_id');
            $table->string('title');
            $table->text('agenda')->nullable();
            $table->dateTime('meeting_date');
            $table->string('location')->nullable();
            $table->text('notes')->nullable();
            $table->timestamps();

            $table->foreign('committee_id')->references('id')->on('committees')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meetings');
    }
}; 