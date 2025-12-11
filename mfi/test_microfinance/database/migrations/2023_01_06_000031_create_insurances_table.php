<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('insurances', function (Blueprint $table) {
            $table->id();
            $table->string('name', 255)->nullable();
            $table->string('category', 10)->nullable();
            $table->decimal('coverage_amount', 10, 2)->nullable();
            $table->decimal('premium', 10, 2)->nullable();
            $table->decimal('monthly_rate', 5, 2)->nullable();
            $table->string('account_number')->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('insurances');
    }
}; 