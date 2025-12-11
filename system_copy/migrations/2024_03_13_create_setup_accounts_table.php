<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('setup_accounts', function (Blueprint $table) {
            $table->id();
            $table->string('sub_category_code', 50)->nullable();
            $table->string('account_number', 20)->unique()->nullable();
            $table->string('account_name', 100)->nullable();
            $table->string('table_name', 150)->nullable();
            $table->string('item', 150)->nullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('setup_accounts');
    }
}; 