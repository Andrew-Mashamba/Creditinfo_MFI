<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('loan_provision_settings', function (Blueprint $table) {
            $table->id();
            $table->string('per', 50)->nullable();
            $table->integer('institution_id')->nullable();
            $table->text('description')->nullable();
            $table->boolean('status')->default(true);
            $table->decimal('percent', 5, 2)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('loan_provision_settings');
    }
}; 