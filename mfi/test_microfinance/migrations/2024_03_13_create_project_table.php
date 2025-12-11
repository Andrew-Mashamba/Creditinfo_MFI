<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('project', function (Blueprint $table) {
            $table->id();
            $table->string('tender_no', 255)->notNullable();
            $table->string('procuring_entity', 255)->notNullable();
            $table->string('supplier_name', 255)->notNullable();
            $table->date('award_date')->notNullable();
            $table->decimal('award_amount', 18, 2)->notNullable();
            $table->string('lot_name', 255)->notNullable();
            $table->date('expected_end_date')->notNullable();
            $table->string('project_summary', 255)->notNullable();
            $table->string('status', 255)->notNullable();
            $table->timestamp('created_at')->useCurrent();
            $table->timestamp('updated_at')->useCurrent();
        });
    }

    public function down()
    {
        Schema::dropIfExists('project');
    }
}; 