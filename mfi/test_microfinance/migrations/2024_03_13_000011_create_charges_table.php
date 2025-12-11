<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('charges', function (Blueprint $table) {
            $table->id();
            $table->integer('institution_number')->nullable();
            $table->integer('branch_number')->nullable();
            $table->integer('charge_number')->nullable();
            $table->string('charge_name')->nullable();
            $table->string('charge_type')->nullable();
            $table->string('flat_charge_amount')->nullable();
            $table->decimal('percentage_charge_amount')->nullable();
            $table->string('status')->nullable();
            $table->timestamps();
            $table->string('product_id', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('charges');
    }
}; 