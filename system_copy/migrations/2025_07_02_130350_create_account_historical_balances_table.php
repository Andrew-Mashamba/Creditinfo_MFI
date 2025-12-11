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
        Schema::create('account_historical_balances', function (Blueprint $table) {
            $table->id();
            $table->integer('year')->comment('Financial year');
            $table->string('account_number', 50)->comment('Account number');
            $table->string('account_name', 200)->comment('Account name');
            $table->string('major_category_code', 20)->comment('Major category (1000=Assets, 2000=Liabilities, 3000=Equity)');
            $table->string('account_level', 50)->comment('Account level (1,2,3,4)');
            $table->string('type')->comment('Account type');
            $table->decimal('balance', 15, 2)->default(0)->comment('Year-end balance');
            $table->decimal('credit', 15, 2)->default(0)->comment('Credit balance');
            $table->decimal('debit', 15, 2)->default(0)->comment('Debit balance');
            $table->timestamp('snapshot_date')->comment('Date when balance was captured');
            $table->string('captured_by')->nullable()->comment('User who captured the balance');
            $table->text('notes')->nullable()->comment('Additional notes');
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['year', 'major_category_code']);
            $table->index(['year', 'account_level']);
            $table->index('account_number');
            $table->unique(['year', 'account_number'], 'unique_year_account');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('account_historical_balances');
    }
};
