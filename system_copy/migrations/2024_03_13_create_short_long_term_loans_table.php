<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('short_long_term_loans', function (Blueprint $table) {
            $table->id();
            $table->integer('source_account_id')->nullable();
            $table->integer('user_id')->nullable();
            $table->string('status', 20)->nullable();
            $table->boolean('is_approved')->default(false);
            $table->decimal('amount', 10, 2)->nullable();
            $table->string('organization_name')->nullable();
            $table->string('address')->nullable();
            $table->string('phone', 15)->nullable();
            $table->string('email')->nullable();
            $table->text('description')->nullable();
            $table->string('application_form')->nullable();
            $table->string('contract_form')->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
            $table->string('loan_type', 50)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('short_long_term_loans');
    }
}; 