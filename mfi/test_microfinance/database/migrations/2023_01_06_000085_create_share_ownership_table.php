<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('share_ownership', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id')->nullable();
            $table->string('client_number')->nullable();
            $table->integer('shares')->default(0)->nullable();
            $table->decimal('total_value', 15, 2)->default(0)->nullable();
            $table->integer('number_of_members')->default(0)->nullable();
            $table->decimal('savings', 15, 2)->default(0)->nullable();
            $table->decimal('deposits', 15, 2)->default(0)->nullable();
            $table->decimal('interest_free_loans', 15, 2)->default(0)->nullable();
            $table->date('end_business_year_date')->nullable();
            $table->timestamps();
            $table->softDeletes();

           
        });
    }

    public function down()
    {
        Schema::dropIfExists('share_ownership');
    }
}; 