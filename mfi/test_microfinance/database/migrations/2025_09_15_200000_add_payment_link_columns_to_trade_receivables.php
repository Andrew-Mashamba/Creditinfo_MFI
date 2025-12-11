<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class AddPaymentLinkColumnsToTradeReceivables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            // Payment link columns
            $table->text('payment_link')->nullable()->after('balance');
            $table->string('payment_link_id')->nullable()->after('payment_link');
            $table->timestamp('payment_link_generated_at')->nullable()->after('payment_link_id');
            
            // Invoice sending tracking
            $table->boolean('invoice_sent')->default(false)->after('payment_link_generated_at');
            $table->timestamp('invoice_sent_at')->nullable()->after('invoice_sent');
            $table->string('invoice_sent_to')->nullable()->after('invoice_sent_at');
            
            // Payment callback tracking
            $table->string('payment_reference')->nullable()->after('invoice_sent_to');
            $table->timestamp('payment_received_at')->nullable()->after('payment_reference');
            $table->string('payment_method')->nullable()->after('payment_received_at');
            
            // Add currency if not exists
            if (!Schema::hasColumn('trade_receivables', 'currency')) {
                $table->string('currency', 10)->default('TZS')->after('amount');
            }
            
            // Add payment terms if not exists
            if (!Schema::hasColumn('trade_receivables', 'payment_terms')) {
                $table->integer('payment_terms')->default(30)->after('due_date');
            }
            
            // Add reference number if not exists
            if (!Schema::hasColumn('trade_receivables', 'reference_number')) {
                $table->string('reference_number')->nullable()->after('invoice_number');
            }
            
            // Add VAT amount tracking if not exists
            if (!Schema::hasColumn('trade_receivables', 'vat_amount')) {
                $table->decimal('vat_amount', 15, 2)->default(0)->after('amount');
            }
            
            // Add indexes for performance
            $table->index('invoice_sent');
            $table->index('payment_link_id');
            // Status index likely already exists, so skip it
        });
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::table('trade_receivables', function (Blueprint $table) {
            // Drop indexes
            $table->dropIndex(['invoice_sent']);
            $table->dropIndex(['payment_link_id']);
            
            // Drop columns
            $table->dropColumn([
                'payment_link',
                'payment_link_id',
                'payment_link_generated_at',
                'invoice_sent',
                'invoice_sent_at',
                'invoice_sent_to',
                'payment_reference',
                'payment_received_at',
                'payment_method'
            ]);
            
            // Drop conditionally added columns
            if (Schema::hasColumn('trade_receivables', 'currency')) {
                $table->dropColumn('currency');
            }
            if (Schema::hasColumn('trade_receivables', 'payment_terms')) {
                $table->dropColumn('payment_terms');
            }
            if (Schema::hasColumn('trade_receivables', 'reference_number')) {
                $table->dropColumn('reference_number');
            }
            if (Schema::hasColumn('trade_receivables', 'vat_amount')) {
                $table->dropColumn('vat_amount');
            }
        });
    }
}