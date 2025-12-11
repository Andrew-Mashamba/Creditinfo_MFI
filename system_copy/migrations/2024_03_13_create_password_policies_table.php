<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('password_policies', function (Blueprint $table) {
            $table->id();
            $table->boolean('requireSpecialCharacter')->notNullable();
            $table->string('length', 10)->notNullable();
            $table->boolean('requireUppercase')->notNullable();
            $table->boolean('requireNumeric')->notNullable();
            $table->integer('limiter')->notNullable();
            $table->integer('passwordExpire')->nullable();
            $table->string('status', 20)->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('password_policies');
    }
}; 