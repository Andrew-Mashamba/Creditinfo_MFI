<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('unearned_deferred_revenue', function (Blueprint $table) {
            $table->id();
            $table->integer('user_id')->nullable();
            $table->integer('source_account_id')->nullable();
            $table->integer('destination_account_id')->nullable();
            $table->string('status', 50)->nullable();
            $table->boolean('is_recognized')->nullable();
            $table->boolean('is_delivery')->nullable();
            $table->text('description')->nullable();
            $table->string('name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 20)->nullable();
            $table->string('email')->nullable();
            $table->timestamps();
            $table->decimal('amount')->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('unearned_deferred_revenue');
    }
}; 