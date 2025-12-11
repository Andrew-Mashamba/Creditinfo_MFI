<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('job_postings', function (Blueprint $table) {
            $table->id();
            $table->string('job_title');
            $table->string('department');
            $table->string('location');
            $table->string('job_type');
            $table->text('description');
            $table->text('requirements');
            $table->decimal('salary', 10, 2);
            $table->enum('status', ['open', 'closed', 'draft'])->default('open');
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('job_postings');
    }
}; 