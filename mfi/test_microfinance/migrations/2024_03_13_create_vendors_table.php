<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('vendors', function (Blueprint $table) {
            $table->id();
            $table->string('organization_name', 40)->nullable();
            $table->string('organization_tin_number', 40)->nullable();
            $table->string('status', 10)->nullable();
            $table->string('email', 60)->nullable();
            $table->string('organization_license_number', 30)->nullable();
            $table->timestamps();
            $table->string('organization_description')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('vendors');
    }
}; 