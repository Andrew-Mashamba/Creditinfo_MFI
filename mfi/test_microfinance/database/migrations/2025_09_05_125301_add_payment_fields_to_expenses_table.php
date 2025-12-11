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
        Schema::table('expenses', function (Blueprint $table) {
            // Payment tracking fields
            $table->timestamp('payment_date')->nullable()->after('status');
            $table->bigInteger('payment_transaction_id')->nullable()->after('payment_date');
            $table->string('payment_method')->nullable()->after('payment_transaction_id');
            $table->string('payment_reference')->nullable()->after('payment_method');
            $table->bigInteger('paid_by_user_id')->nullable()->after('payment_reference');
            $table->decimal('actual_spent', 15, 2)->nullable()->after('paid_by_user_id');
            $table->timestamp('last_payment_date')->nullable()->after('actual_spent');
            $table->decimal('last_payment_amount', 15, 2)->nullable()->after('last_payment_date');
            
            // Add indexes for better query performance
            $table->index('payment_date');
            $table->index('payment_transaction_id');
            $table->index('paid_by_user_id');
            $table->index('payment_reference');
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('expenses', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['payment_date']);
            $table->dropIndex(['payment_transaction_id']);
            $table->dropIndex(['paid_by_user_id']);
            $table->dropIndex(['payment_reference']);
            
            // Drop columns
            $table->dropColumn([
                'payment_date',
                'payment_transaction_id',
                'payment_method',
                'payment_reference',
                'paid_by_user_id',
                'actual_spent',
                'last_payment_date',
                'last_payment_amount'
            ]);
        });
    }
};
