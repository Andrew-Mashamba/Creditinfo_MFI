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
        Schema::create('payment_notifications', function (Blueprint $table) {
            $table->id();
            $table->string('control_number', 13);
            $table->timestamp('received_at');
            $table->json('raw_payload');
            $table->enum('status', ['Pending', 'Processed', 'Failed'])->default('Pending');
            $table->timestamp('processed_at')->nullable();
            $table->timestamps();

            $table->index('control_number');
            $table->index('status');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('payment_notifications');
    }
};
