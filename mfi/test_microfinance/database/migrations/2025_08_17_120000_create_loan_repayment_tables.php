<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

class CreateLoanRepaymentTables extends Migration
{
    /**
     * Run the migrations.
     *
     * @return void
     */
    public function up()
    {
        // Create loan_payments table if it doesn't exist
        if (!Schema::hasTable('loan_payments')) {
            Schema::create('loan_payments', function (Blueprint $table) {
                $table->id();
                $table->string('loan_id');
                $table->datetime('payment_date');
                $table->decimal('amount', 15, 2);
                $table->decimal('principal_paid', 15, 2)->default(0);
                $table->decimal('interest_paid', 15, 2)->default(0);
                $table->decimal('penalties_paid', 15, 2)->default(0);
                $table->decimal('overpayment', 15, 2)->default(0);
                $table->string('payment_method', 50);
                $table->string('reference_number')->nullable();
                $table->string('receipt_number')->unique();
                $table->string('processed_by')->nullable();
                $table->text('narration')->nullable();
                $table->string('status')->default('COMPLETED');
                $table->timestamps();
                
                $table->index('loan_id');
                $table->index('payment_date');
                $table->index('receipt_number');
            });
        }

        // Create loan_advance_payments table for overpayments if it doesn't exist
        if (!Schema::hasTable('loan_advance_payments')) {
            Schema::create('loan_advance_payments', function (Blueprint $table) {
                $table->id();
                $table->string('loan_id');
                $table->decimal('amount', 15, 2);
                $table->string('status')->default('AVAILABLE'); // AVAILABLE, APPLIED, REFUNDED
                $table->datetime('applied_date')->nullable();
                $table->text('notes')->nullable();
                $table->timestamps();
                
                $table->index('loan_id');
                $table->index('status');
            });
        }

        // Add missing columns to loans_schedules if they don't exist
        if (Schema::hasTable('loans_schedules')) {
            Schema::table('loans_schedules', function (Blueprint $table) {
                if (!Schema::hasColumn('loans_schedules', 'interest_payment')) {
                    $table->decimal('interest_payment', 15, 2)->default(0)->after('interest');
                }
                if (!Schema::hasColumn('loans_schedules', 'principle_payment')) {
                    $table->decimal('principle_payment', 15, 2)->default(0)->after('principle');
                }
                if (!Schema::hasColumn('loans_schedules', 'last_payment_date')) {
                    $table->datetime('last_payment_date')->nullable();
                }
            });
        }

        // Add missing columns to loans table if they don't exist
        if (Schema::hasTable('loans')) {
            Schema::table('loans', function (Blueprint $table) {
                if (!Schema::hasColumn('loans', 'days_in_arrears')) {
                    $table->integer('days_in_arrears')->default(0);
                }
                if (!Schema::hasColumn('loans', 'arrears_in_amount')) {
                    $table->decimal('arrears_in_amount', 15, 2)->default(0);
                }
                if (!Schema::hasColumn('loans', 'closure_date')) {
                    $table->date('closure_date')->nullable();
                }
                if (!Schema::hasColumn('loans', 'loan_status')) {
                    $table->string('loan_status')->nullable();
                }
            });
        }
    }

    /**
     * Reverse the migrations.
     *
     * @return void
     */
    public function down()
    {
        Schema::dropIfExists('loan_advance_payments');
        Schema::dropIfExists('loan_payments');
        
        if (Schema::hasTable('loans_schedules')) {
            Schema::table('loans_schedules', function (Blueprint $table) {
                if (Schema::hasColumn('loans_schedules', 'interest_payment')) {
                    $table->dropColumn('interest_payment');
                }
                if (Schema::hasColumn('loans_schedules', 'principle_payment')) {
                    $table->dropColumn('principle_payment');
                }
                if (Schema::hasColumn('loans_schedules', 'last_payment_date')) {
                    $table->dropColumn('last_payment_date');
                }
            });
        }
        
        if (Schema::hasTable('loans')) {
            Schema::table('loans', function (Blueprint $table) {
                if (Schema::hasColumn('loans', 'days_in_arrears')) {
                    $table->dropColumn('days_in_arrears');
                }
                if (Schema::hasColumn('loans', 'arrears_in_amount')) {
                    $table->dropColumn('arrears_in_amount');
                }
                if (Schema::hasColumn('loans', 'closure_date')) {
                    $table->dropColumn('closure_date');
                }
                if (Schema::hasColumn('loans', 'loan_status')) {
                    $table->dropColumn('loan_status');
                }
            });
        }
    }
}