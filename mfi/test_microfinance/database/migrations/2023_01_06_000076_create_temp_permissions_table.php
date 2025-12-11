<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('temp_permissions', function (Blueprint $table) {
            $table->id();
            $table->integer('role_id')->nullable();
            // Generate t1 through t200 columns
            for ($i = 1; $i <= 200; $i++) {
                $table->integer("t{$i}")->nullable();
            }
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('temp_permissions');
    }
}; 