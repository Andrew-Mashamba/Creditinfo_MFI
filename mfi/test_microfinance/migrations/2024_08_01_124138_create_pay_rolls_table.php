<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {

        Schema::create('pay_rolls', function (Blueprint $table) {
            $table->id(); // This creates an auto-incrementing primary key column named 'id'
            $table->unsignedBigInteger('employee_id');
            $table->date('pay_period_start');
            $table->date('pay_period_end');
            $table->decimal('gross_salary', 10, 2);
            $table->decimal('hours_worked', 5, 2)->nullable();
            $table->decimal('overtime_hours', 5, 2)->nullable();
            $table->decimal('tax_deductions', 10, 2)->nullable();
            $table->decimal('social_security', 10, 2)->nullable();
            $table->decimal('medicare', 10, 2)->nullable();
            $table->decimal('retirement_contributions', 10, 2)->nullable();
            $table->decimal('health_insurance', 10, 2)->nullable();
            $table->decimal('other_deductions', 10, 2)->nullable();
            $table->decimal('total_deductions', 10, 2)->storedAs('COALESCE(tax_deductions, 0) + COALESCE(social_security, 0) + COALESCE(medicare, 0) + COALESCE(retirement_contributions, 0) + COALESCE(health_insurance, 0) + COALESCE(other_deductions, 0)');
            $table->decimal('net_salary', 10, 2)->storedAs('gross_salary - (COALESCE(tax_deductions, 0) + COALESCE(social_security, 0) + COALESCE(medicare, 0) + COALESCE(retirement_contributions, 0) + COALESCE(health_insurance, 0) + COALESCE(other_deductions, 0))');
            $table->string('payment_method')->nullable();
            $table->date('payment_date')->nullable();
            $table->foreign('employee_id')->references('id')->on('employees')->onDelete('cascade');
            $table->timestamps();
        });


    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('pay_rolls');
    }
};
