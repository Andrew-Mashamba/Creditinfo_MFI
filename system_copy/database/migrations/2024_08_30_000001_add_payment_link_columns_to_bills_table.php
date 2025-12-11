<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            // Add payment link columns if they don't exist
            if (!Schema::hasColumn('bills', 'payment_link')) {
                $table->string('payment_link')->nullable()->after('payment_status');
            }
            
            if (!Schema::hasColumn('bills', 'payment_link_id')) {
                $table->string('payment_link_id')->nullable()->after('payment_link');
            }
            
            if (!Schema::hasColumn('bills', 'payment_link_generated_at')) {
                $table->timestamp('payment_link_generated_at')->nullable()->after('payment_link_id');
            }
            
            if (!Schema::hasColumn('bills', 'payment_link_items')) {
                $table->json('payment_link_items')->nullable()->after('payment_link_generated_at');
            }
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('bills', function (Blueprint $table) {
            $table->dropColumn([
                'payment_link',
                'payment_link_id', 
                'payment_link_generated_at',
                'payment_link_items'
            ]);
        });
    }
};
