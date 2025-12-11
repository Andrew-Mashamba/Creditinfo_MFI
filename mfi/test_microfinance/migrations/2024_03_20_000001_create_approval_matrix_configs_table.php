<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('approval_matrix_configs', function (Blueprint $table) {
            $table->id();
            $table->string('process_type'); // e.g., 'loan', 'expense', 'budget', 'hire'
            $table->string('process_name');
            $table->string('process_code');
            $table->integer('level');
            $table->string('approver_role'); // e.g., 'branch_manager', 'general_manager', 'board_chair'
            $table->string('approver_sub_role')->nullable(); // e.g., 'loan_manager', 'account_manager'
            $table->decimal('min_amount', 15, 2)->nullable();
            $table->decimal('max_amount', 15, 2)->nullable();
            $table->boolean('is_active')->default(true);
            $table->json('additional_conditions')->nullable(); // For complex approval rules
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('approval_matrix_configs');
    }
}; 