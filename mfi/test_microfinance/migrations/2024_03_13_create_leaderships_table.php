<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaderships', function (Blueprint $table) {
            $table->id();
            $table->string('full_name', 40)->nullable();
            $table->string('type')->default('management');
            $table->bigInteger('institution_id')->nullable();
            $table->string('image', 255)->nullable();
            $table->string('position', 50)->nullable();
            $table->text('leaderDescriptions')->nullable();
            $table->string('approval_option', 20)->nullable();
            $table->string('startDate', 30)->nullable();
            $table->string('endDate', 30)->nullable();
            $table->string('member_number', 255)->nullable();
            $table->boolean('is_signatory')->default(false);
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaderships');
    }
}; 