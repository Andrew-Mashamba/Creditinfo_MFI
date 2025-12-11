<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    public function up()
    {
        Schema::create('investments_list', function (Blueprint $table) {
            $table->id();
            $table->string('investment_type', 255)->notNullable();
            $table->decimal('principal_amount', 15, 0)->notNullable();
            $table->date('investment_date')->notNullable();
            $table->integer('number_of_shares')->nullable();
            $table->decimal('share_price', 15, 0)->nullable();
            $table->decimal('brokerage_fees', 15, 0)->nullable();
            $table->decimal('dividend_rate', 50, 0)->nullable();
            $table->decimal('sale_price', 15, 0)->nullable();
            $table->decimal('interest_rate', 50, 0)->nullable();
            $table->integer('tenure')->nullable();
            $table->date('maturity_date')->nullable();
            $table->decimal('penalty', 15, 0)->nullable();
            $table->string('bond_type', 255)->nullable();
            $table->decimal('coupon_rate', 50, 0)->nullable();
            $table->decimal('bond_yield', 50, 0)->nullable();
            $table->string('fund_name', 255)->nullable();
            $table->string('fund_manager', 255)->nullable();
            $table->decimal('expense_ratio', 50, 0)->nullable();
            $table->decimal('nav', 15, 0)->nullable();
            $table->integer('units_purchased')->nullable();
            $table->decimal('property_value', 15, 0)->nullable();
            $table->string('location', 255)->nullable();
            $table->date('purchase_date')->nullable();
            $table->decimal('annual_property_taxes', 15, 0)->nullable();
            $table->decimal('rental_income', 15, 0)->nullable();
            $table->decimal('maintenance_costs', 15, 0)->nullable();
            $table->text('description')->nullable();
            $table->decimal('interest_dividend_rate', 50, 0)->nullable();
            $table->string('status', 150)->nullable();
            $table->string('cash_account', 250)->nullable();
            $table->string('investment_account', 250)->nullable();
            $table->timestamps();
        });
    }

    public function down()
    {
        Schema::dropIfExists('investments_list');
    }
}; 