<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('meeting_documents', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('meeting_id');
            $table->string('file_name');
            $table->string('file_path');
            $table->unsignedBigInteger('uploaded_by')->nullable(); // leader_id
            $table->timestamps();

            $table->foreign('meeting_id')->references('id')->on('meetings')->onDelete('cascade');
            $table->foreign('uploaded_by')->references('id')->on('leaderships')->onDelete('set null');
        });
    }

    public function down()
    {
        Schema::dropIfExists('meeting_documents');
    }
}; 