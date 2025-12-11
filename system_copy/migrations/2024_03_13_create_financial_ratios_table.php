<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;
use Illuminate\Support\Facades\DB;

return new class extends Migration
{
    public function up()
    {
        Schema::create('financial_ratios', function (Blueprint $table) {
            $table->id();
            $table->date('end_of_financial_year_date')->notNullable();
            $table->decimal('core_capital', 20, 2)->notNullable();
            $table->decimal('total_assets', 20, 2)->notNullable();
            $table->decimal('net_capital', 20, 2)->notNullable();
            $table->decimal('short_term_assets', 20, 2)->notNullable();
            $table->decimal('short_term_liabilities', 20, 2)->notNullable();
            $table->decimal('expenses', 20, 2)->notNullable();
            $table->decimal('income', 20, 2)->notNullable();
            $table->timestamps();
        });

        // Add table comment
        // DB::statement("COMMENT ON TABLE financial_ratios IS 'Stores various financial ratios related to company performance for each fiscal year.'");
    }

    public function down()
    {
        Schema::dropIfExists('financial_ratios');
    }
};
 