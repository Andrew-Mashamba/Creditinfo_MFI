<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('settled_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('loan_id')->nullable();
            $table->integer('loan_array_id')->nullable();
            $table->decimal('amount', 12, 2)->nullable();
            $table->string('institution')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->boolean('is_selected')->default(false);
            $table->string('account', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('settled_loans');
    }
}; 