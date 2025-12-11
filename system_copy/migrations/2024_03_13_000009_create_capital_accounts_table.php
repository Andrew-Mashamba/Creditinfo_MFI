<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('capital_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('major_category_code')->nullable();
            $table->string('category_code')->nullable();
            $table->string('category_name')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('capital_accounts');
    }
}; 