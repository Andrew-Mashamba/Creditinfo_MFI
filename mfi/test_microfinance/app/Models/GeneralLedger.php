<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class GeneralLedger extends Model
{
    use HasFactory;
    
    protected $table = 'general_ledger';
    
    protected $fillable = [
        'record_on_account_number',
        'record_on_account_number_balance',
        'sender_branch_id',
        'beneficiary_branch_id',
        'sender_product_id',
        'sender_sub_product_id',
        'beneficiary_product_id',
        'beneficiary_sub_product_id',
        'sender_id',
        'beneficiary_id',
        'sender_name',
        'beneficiary_name',
        'sender_account_number',
        'beneficiary_account_number',
        'transaction_type',
        'sender_account_currency_type',
        'beneficiary_account_currency_type',
        'narration',
        'branch_id',
        'credit',
        'debit',
        'reference_number',
        'trans_status',
        'trans_status_description',
        'swift_code',
        'destination_bank_name',
        'destination_bank_number',
        'partner_bank',
        'partner_bank_name',
        'partner_bank_account_number',
        'partner_bank_transaction_reference_number',
        'payment_status',
        'recon_status',
        'loan_id',
        'bank_reference_number',
        'product_number',
        'major_category_code',
        'category_code',
        'sub_category_code',
        'gl_balance',
        'account_level',
        'budget_id', // Link to budget
        'budget_transaction_id' // Link to budget transaction
    ];
    
    protected $casts = [
        'record_on_account_number_balance' => 'decimal:2',
        'credit' => 'decimal:2',
        'debit' => 'decimal:2',
        'gl_balance' => 'decimal:2',
        'created_at' => 'datetime',
        'updated_at' => 'datetime'
    ];
    
    /**
     * Get the budget associated with this GL entry
     */
    public function budget()
    {
        return $this->belongsTo(BudgetManagement::class, 'budget_id');
    }
    
    /**
     * Get the budget transaction
     */
    public function budgetTransaction()
    {
        return $this->belongsTo(BudgetTransaction::class, 'budget_transaction_id');
    }
    
    /**
     * Scope for expense transactions
     */
    public function scopeExpenses($query)
    {
        return $query->where('major_category_code', '5000')
            ->where('debit', '>', 0);
    }
    
    /**
     * Scope for a specific budget
     */
    public function scopeForBudget($query, $budgetId)
    {
        return $query->where('budget_id', $budgetId);
    }
    
    /**
     * Scope for a date range
     */
    public function scopeForPeriod($query, $startDate, $endDate)
    {
        return $query->whereBetween('created_at', [$startDate, $endDate]);
    }
    
    /**
     * Get the account associated with this entry
     */
    public function account()
    {
        return $this->belongsTo(AccountsModel::class, 'record_on_account_number', 'account_number');
    }
}