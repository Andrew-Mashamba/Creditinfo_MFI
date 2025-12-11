<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('query_responses', function (Blueprint $table) {
            $table->id();
            $table->string('message_id', 255)->notNullable();
            $table->string('connector_id', 255)->notNullable();
            $table->string('type', 50)->notNullable();
            $table->string('message', 255)->nullable();
            $table->jsonb('response_data')->nullable();
            $table->timestamp('timestamp')->notNullable();
            $table->string('CheckNumber', 50)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('query_responses');
    }
}; 