<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('committee_approvals', function (Blueprint $table) {
            $table->id();
            $table->foreignId('committee_id')->constrained()->onDelete('cascade');
            $table->foreignId('user_id')->constrained()->onDelete('cascade');
            $table->foreignId('loan_id')->constrained('loans')->onDelete('cascade');
            $table->string('status'); // approved, rejected, pending
            $table->text('comments')->nullable();
            $table->integer('approval_order');
            $table->timestamps();

            $table->unique(['committee_id', 'loan_id', 'user_id']);
        });
    }

    public function down()
    {
        Schema::dropIfExists('committee_approvals');
    }
}; 