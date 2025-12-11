<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_position', function (Blueprint $table) {
            $table->id();
            $table->decimal('interest_on_loans', 15, 2)->default(0.00);
            $table->decimal('other_income', 15, 2)->default(0.00);
            $table->decimal('total_income', 15, 2)->default(0.00);
            $table->decimal('expenses', 15, 2)->default(0.00);
            $table->decimal('annual_surplus', 15, 2)->default(0.00);
            $table->date('end_of_business_year')->notNullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('financial_position');
    }
}; 