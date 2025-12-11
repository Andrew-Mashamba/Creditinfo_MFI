<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('institution_files', function (Blueprint $table) {
            $table->id();
            $table->unsignedBigInteger('institution_id')->nullable(); // Link to institutions if needed
            $table->unsignedInteger('file_id'); // Corresponds to the fileFields key in the component
            $table->string('file_name');
            $table->string('file_path');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('institution_files');
    }
}; 