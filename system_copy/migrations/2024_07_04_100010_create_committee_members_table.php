<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('committee_members', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('committee_id');
            $table->unsignedBigInteger('leader_id');
            $table->string('role')->nullable(); // chair, secretary, member, etc.
            $table->date('joined_at')->nullable();
            $table->date('left_at')->nullable();
            $table->timestamps();

            $table->foreign('committee_id')->references('id')->on('committees')->onDelete('cascade');
            $table->foreign('leader_id')->references('id')->on('leaderships')->onDelete('cascade');
        });
    }

    public function down()
    {
        Schema::dropIfExists('committee_members');
    }
}; 