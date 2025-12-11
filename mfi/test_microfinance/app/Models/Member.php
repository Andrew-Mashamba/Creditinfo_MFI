<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class Member extends Model
{
    use HasFactory;

    protected $guarded = [];
    protected $table = 'clients';

    /**
     * Get all bills associated with the member
     */
    public function bills(): HasMany
    {
        return $this->hasMany(Bill::class);
    }

    /**
     * Get all shares owned by the member
     */
    public function shares(): HasMany
    {
        return $this->hasMany(Share::class);
    }

    /**
     * Get all accounts owned by the member
     */
    public function accounts(): HasMany
    {
        return $this->hasMany(Account::class);
    }

    /**
     * Get all transactions made by the member
     */
    public function transactions(): HasMany
    {
        return $this->hasMany(Transaction::class);
    }

    /**
     * Get all dividend payments received by the member
     */
    public function dividendPayments(): HasMany
    {
        return $this->hasMany(DividendPayment::class);
    }

    /**
     * Get all loan applications made by the member
     */
    public function loanApplications(): HasMany
    {
        return $this->hasMany(LoanApplication::class);
    }

    /**
     * Get all loan repayments made by the member
     */
    public function loanRepayments(): HasMany
    {
        return $this->hasMany(LoanRepayment::class);
    }

    /**
     * Get all savings accounts owned by the member
     */
    public function savingsAccounts(): HasMany
    {
        return $this->hasMany(SavingsAccount::class);
    }

    /**
     * Get all fixed deposits owned by the member
     */
    public function fixedDeposits(): HasMany
    {
        return $this->hasMany(FixedDeposit::class);
    }

    /**
     * Get all notifications for the member
     */
    public function notifications(): HasMany
    {
        return $this->hasMany(Notification::class);
    }

    /**
     * Get the member's next of kin
     */
    public function nextOfKin(): HasOne
    {
        return $this->hasOne(NextOfKin::class);
    }

    /**
     * Get all documents uploaded by the member
     */
    public function documents(): HasMany
    {
        return $this->hasMany(Document::class);
    }

    /**
     * Get all audit logs related to the member
     */
    public function auditLogs(): HasMany
    {
        return $this->hasMany(AuditLog::class);
    }

    /**
     * Get all system activities related to the member
     */
    public function systemActivities(): HasMany
    {
        return $this->hasMany(SystemActivity::class);
    }
}
