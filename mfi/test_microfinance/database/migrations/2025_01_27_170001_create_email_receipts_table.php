<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::create('email_receipts', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('email_id');
            $table->enum('type', ['read', 'delivery']);
            $table->unsignedBigInteger('reader_id')->nullable();
            $table->string('reader_email')->nullable();
            $table->string('reader_ip')->nullable();
            $table->text('user_agent')->nullable();
            $table->timestamp('created_at');
            
            $table->foreign('email_id')->references('id')->on('emails')->onDelete('cascade');
            $table->foreign('reader_id')->references('id')->on('users')->onDelete('set null');
            $table->index(['email_id', 'type']);
            $table->index('created_at');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('email_receipts');
    }
};