<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('entries_amount', function (Blueprint $table) {
            $table->id();
            $table->string('entry_id')->nullable();
            $table->string('account_id')->nullable();
            $table->string('amount')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('entries_amount');
    }
}; 