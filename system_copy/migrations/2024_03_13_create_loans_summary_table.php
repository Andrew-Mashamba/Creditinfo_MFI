<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loans_summary', function (Blueprint $table) {
            $table->id();
            $table->string('loan_id', 255)->notNullable();
            $table->double('installment')->nullable();
            $table->double('interest')->nullable();
            $table->double('principle')->nullable();
            $table->double('balance')->nullable();
            $table->string('completion_status', 50)->default('ACTIVE');
            $table->string('status', 50)->default('ACTIVE');
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loans_summary');
    }
}; 