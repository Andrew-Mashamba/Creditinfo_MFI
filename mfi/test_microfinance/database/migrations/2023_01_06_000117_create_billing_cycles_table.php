<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('billing_cycles', function (Blueprint $table) {
            $table->id();
            $table->string('month_year', 7); // Format: YYYY-MM
            $table->date('start_date');
            $table->date('end_date');
            $table->enum('status', ['Open', 'Closed'])->default('Open');
            $table->timestamps();

            $table->unique('month_year');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('billing_cycles');
    }
};
