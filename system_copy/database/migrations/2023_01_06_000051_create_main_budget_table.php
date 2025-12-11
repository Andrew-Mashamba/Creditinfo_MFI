<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('main_budget', function (Blueprint $table) {
            $table->id();
            $table->string('institution_id', 20)->nullable();
            $table->string('sub_category_code', 255)->nullable();
            $table->string('sub_category_name', 255)->nullable();
            $table->decimal('january', 10, 2)->nullable();
            $table->decimal('january_init', 10, 2)->nullable();
            $table->decimal('february', 10, 2)->nullable();
            $table->decimal('february_init', 10, 2)->nullable();
            $table->decimal('march', 10, 2)->nullable();
            $table->decimal('march_init', 10, 2)->nullable();
            $table->decimal('april', 10, 2)->nullable();
            $table->decimal('april_init', 10, 2)->nullable();
            $table->decimal('may', 10, 2)->nullable();
            $table->decimal('may_init', 10, 2)->nullable();
            $table->decimal('june', 10, 2)->nullable();
            $table->decimal('june_init', 10, 2)->nullable();
            $table->decimal('july', 10, 2)->nullable();
            $table->decimal('july_init', 10, 2)->nullable();
            $table->decimal('august', 10, 2)->nullable();
            $table->decimal('august_init', 10, 2)->nullable();
            $table->decimal('september', 10, 2)->nullable();
            $table->decimal('september_init', 10, 2)->nullable();
            $table->decimal('october', 10, 2)->nullable();
            $table->decimal('october_init', 10, 2)->nullable();
            $table->decimal('november', 10, 2)->nullable();
            $table->decimal('november_init', 10, 2)->nullable();
            $table->decimal('december', 10, 2)->nullable();
            $table->decimal('december_init', 10, 2)->nullable();
            $table->decimal('total', 10, 2)->nullable();
            $table->decimal('total_init', 10, 2)->nullable();
            $table->string('year', 20)->nullable();
            $table->string('type', 150)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('main_budget');
    }
}; 