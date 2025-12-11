<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_stages', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_product_id')->notNullable();
            $table->integer('stage_id')->notNullable();
            $table->string('stage_type', 50)->notNullable();
            $table->string('status', 50)->notNullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_stages');
    }
}; 