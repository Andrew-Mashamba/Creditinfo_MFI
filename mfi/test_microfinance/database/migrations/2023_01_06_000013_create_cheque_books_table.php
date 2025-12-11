<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('cheque_books', function (Blueprint $table) {
            $table->id();
            $table->bigInteger('bank');
            $table->bigInteger('chequeBook_id');
            $table->bigInteger('remaining_leaves')->nullable();
            $table->string('leave_number', 50)->nullable();
            $table->bigInteger('institution_id');
            $table->bigInteger('branch_id');
            $table->string('status', 20)->default('PENDING');
            $table->timestamp('created_at')->default('2024-06-05 15:01:34');
            $table->timestamp('updated_at')->default('2024-06-05 15:01:34');
        });
    }

    public function down()
    {
        Schema::dropIfExists('cheque_books');
    }
}; 