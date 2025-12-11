<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loss_reserves', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->notNullable();
            $table->decimal('profits', 15, 2)->nullable();
            $table->decimal('percentage', 5, 2)->nullable();
            $table->decimal('reserve_amount', 15, 2)->nullable();
            $table->decimal('adjustments', 15, 2)->default(0);
            $table->decimal('total_allocation', 15, 2)->nullable();
            $table->string('status', 50)->nullable();
            $table->string('initial_allocation', 150)->nullable();
            $table->string('profitsx', 150)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loss_reserves');
    }
}; 