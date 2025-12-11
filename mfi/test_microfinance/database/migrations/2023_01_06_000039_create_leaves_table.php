<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('leaves', function (Blueprint $table) {
            $table->id();
            $table->integer('employee_id')->notNullable();
            $table->string('leave_type', 50)->notNullable();
            $table->date('start_date')->notNullable();
            $table->date('end_date')->notNullable();
            $table->string('status', 255)->notNullable();
            $table->text('reason')->nullable();
            $table->string('description', 200)->nullable();
            $table->timestamps();

            // Add check constraint for status
            // $table->check("status IN ('pending', 'approved', 'rejected')");
        });
    }

    public function down()
    {
        Schema::dropIfExists('leaves');
    }
}; 