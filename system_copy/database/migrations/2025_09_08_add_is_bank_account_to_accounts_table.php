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
        Schema::table('accounts', function (Blueprint $table) {
            if (!Schema::hasColumn('accounts', 'is_bank_account')) {
                $table->boolean('is_bank_account')->default(false)->after('account_type');
                $table->index('is_bank_account');
            }
        });
        
        // Mark existing bank accounts
        DB::table('accounts')
            ->where(function($query) {
                $query->where('account_name', 'like', '%bank%')
                      ->orWhere('account_name', 'like', '%cash%')
                      ->orWhere('category_code', '101') // Cash and bank equivalents
                      ->orWhere('category_code', '102'); // Bank accounts
            })
            ->where('account_type', 'ASSET')
            ->update(['is_bank_account' => true]);
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::table('accounts', function (Blueprint $table) {
            if (Schema::hasColumn('accounts', 'is_bank_account')) {
                $table->dropColumn('is_bank_account');
            }
        });
    }
};