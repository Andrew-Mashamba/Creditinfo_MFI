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
        Schema::create('historical_balances', function (Blueprint $table) {
            $table->id();
            $table->string('institution_number', 120)->nullable();
            $table->string('branch_number', 120)->nullable();
            $table->string('major_category_code', 20)->nullable();
            $table->string('category_code', 20)->nullable();
            $table->string('sub_category_code', 20)->nullable();
            $table->decimal('balance', 20, 2)->default(0);
            $table->date('balance_date');
            $table->string('account_type', 50)->nullable(); // asset_accounts, liability_accounts, etc.
            $table->text('notes')->nullable();
            $table->timestamps();
            
            // Indexes for better performance
            $table->index(['major_category_code', 'category_code', 'balance_date']);
            $table->index(['institution_number', 'branch_number']);
            $table->index('balance_date');
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('historical_balances');
    }
};
