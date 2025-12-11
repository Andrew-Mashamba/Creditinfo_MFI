<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('call_back', function (Blueprint $table) {
            $table->id();
            $table->text('status')->nullable();
            $table->timestamp('created_at')->default('2024-06-05 15:01:34');
            $table->timestamp('updated_at')->default('2024-06-05 15:01:34');
        });
    }

    public function down()
    {
        Schema::dropIfExists('call_back');
    }
}; 