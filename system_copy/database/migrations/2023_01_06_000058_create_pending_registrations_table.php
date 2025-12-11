<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('pending_registrations', function (Blueprint $table) {
            $table->id();
            $table->string('reference_number', 30)->nullable();
            $table->double('amount')->nullable();
            $table->string('account_id', 40)->nullable();
            $table->integer('branch_id')->nullable();
            $table->string('phone_number', 30)->nullable();
            $table->string('status', 20)->nullable();
            $table->string('nida_number', 50)->nullable();
            $table->decimal('required_amount', 15, 2)->nullable();
            $table->timestamp('created_at')->nullable();
            $table->timestamp('updated_at')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('pending_registrations');
    }
}; 