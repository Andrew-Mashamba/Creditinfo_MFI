<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('sub_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('institution_number', 120)->nullable();
            $table->string('branch_number', 120)->nullable();
            $table->string('client_number', 120)->nullable();
            $table->string('account_use', 120)->nullable();
            $table->string('product_number', 120)->nullable();
            $table->string('sub_product_number', 120)->nullable();
            $table->string('major_category_code', 20)->nullable();
            $table->string('category_code', 20)->nullable();
            $table->string('sub_category_code', 20)->nullable();
            $table->string('account_name', 200)->nullable();
            $table->string('account_number', 50)->nullable();
            $table->double('balance')->default(0)->notNullable();
            $table->text('notes')->nullable();
            $table->string('status', 100)->default('PENDING')->notNullable();
            $table->string('mirror_account')->nullable();
            $table->timestamp('created_at')->nullable();
            $table->string('employee_id')->nullable();
            $table->timestamp('updated_at')->notNullable()->useCurrent();
            $table->string('phone_number', 30)->nullable();
            $table->decimal('locked_amount', 15, 2)->nullable();
            $table->string('suspense_account')->nullable();
            $table->integer('bank_id')->nullable();
            $table->string('account_level', 50)->nullable();
            $table->string('parent_account_number', 150)->nullable();
            $table->string('type', 150)->nullable();
            $table->string('debit', 150)->nullable();
            $table->string('credit', 150)->nullable();
        });
    }

    public function down()
    {
        Schema::dropIfExists('sub_accounts');
    }
}; 