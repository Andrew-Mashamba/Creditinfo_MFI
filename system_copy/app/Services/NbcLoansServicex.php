<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;

class NbcLoansServicex
{
    protected $clientNumber;
    protected $selectedLoanId;

    public function __construct($clientNumber, $selectedLoanId = null)
    {
        $this->clientNumber = $clientNumber;
        $this->selectedLoanId = $selectedLoanId;
    }

    public function getNbcLoansData()
    {
        try {
            $loans = $this->getClientLoans();
            $summary = $this->getLoansSummary($loans);
            
            return [
                'loans' => $loans,
                'summary' => $summary,
                'has_loans' => count($loans) > 0,
                'total_balance' => $summary['total_balance'],
                'total_installment' => $summary['total_installment'],
                'selected_loan' => $this->selectedLoanId
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting NBC loans data: ' . $e->getMessage());
            return [
                'loans' => [],
                'summary' => ['total_balance' => 0, 'total_installment' => 0, 'total_loans' => 0],
                'has_loans' => false,
                'total_balance' => 0,
                'total_installment' => 0,
                'selected_loan' => null
            ];
        }
    }

    protected function getClientLoans()
    {
        $loans = DB::table('loans')
            ->where('client_number', $this->clientNumber)
            ->when($this->selectedLoanId, function($query) {
                return $query->where('id', $this->selectedLoanId);
            })
            ->get();

        $enhancedLoans = [];
        
        foreach ($loans as $loan) {
            $enhancedLoans[] = $this->enhanceLoanData($loan);
        }

        return $enhancedLoans;
    }

    protected function enhanceLoanData($loan)
    {
        $product = DB::table('loan_sub_products')
            ->where('sub_product_id', $loan->loan_sub_product)
            ->first();

        $accountBalance = DB::table('accounts')
            ->where('account_number', $loan->loan_account_number)
            ->value('balance') ?? 0;

        $subAccountBalance = DB::table('sub_accounts')
            ->where('account_number', $loan->loan_account_number)
            ->value('balance') ?? 0;

        $loanSummary = DB::table('loans_summary')
            ->where('loan_id', $loan->loan_id)
            ->first();

        $status = $this->calculateLoanStatus($loan, $accountBalance, $loanSummary);
        $daysOverdue = $this->calculateDaysOverdue($loan, $loanSummary);

        return [
            'id' => $loan->id,
            'loan_id' => $loan->loan_id,
            'loan_account_number' => $loan->loan_account_number,
            'loan_sub_product' => $loan->loan_sub_product,
            'principle' => $loan->principle,
            'status' => $loan->status,
            'product_name' => $product->sub_product_name ?? 'Unknown Product',
            'product_id' => $product->sub_product_id ?? null,
            'account_balance' => $accountBalance,
            'sub_account_balance' => $subAccountBalance,
            'installment' => $loanSummary->installment ?? 0,
            'loan_status' => $status,
            'days_overdue' => $daysOverdue,
            'is_selected' => $loan->id == $this->selectedLoanId,
            'risk_level' => $this->calculateRiskLevel($accountBalance, $loanSummary),
            'last_payment_date' => $loanSummary->last_payment_date ?? null,
            'next_payment_date' => $loanSummary->next_payment_date ?? null,
            'total_paid' => $loanSummary->total_paid ?? 0,
            'remaining_balance' => $accountBalance,
            'loan_age_days' => $this->calculateLoanAge($loan->created_at ?? now()),
            'payment_frequency' => $product->payment_frequency ?? 'Monthly'
        ];
    }

    protected function calculateLoanStatus($loan, $balance, $loanSummary)
    {
        if (!$loanSummary) {
            return 'No Data';
        }

        $nextPaymentDate = $loanSummary->next_payment_date ?? null;

        if ($balance <= 0) {
            return 'Paid Off';
        }

        if ($nextPaymentDate && now()->gt($nextPaymentDate)) {
            $daysOverdue = now()->diffInDays($nextPaymentDate);
            if ($daysOverdue > 90) {
                return 'Seriously Overdue';
            } elseif ($daysOverdue > 30) {
                return 'Overdue';
            } else {
                return 'Late';
            }
        }

        return 'Active';
    }

    protected function calculateDaysOverdue($loan, $loanSummary)
    {
        if (!$loanSummary || !$loanSummary->next_payment_date) {
            return 0;
        }

        $nextPaymentDate = \Carbon\Carbon::parse($loanSummary->next_payment_date);
        
        if (now()->gt($nextPaymentDate)) {
            return now()->diffInDays($nextPaymentDate);
        }

        return 0;
    }

    protected function calculateRiskLevel($balance, $loanSummary)
    {
        if (!$loanSummary) {
            return 'Unknown';
        }

        $installment = $loanSummary->installment ?? 0;
        $daysOverdue = $this->calculateDaysOverdue(null, $loanSummary);

        if ($daysOverdue > 90) {
            return 'High Risk';
        } elseif ($daysOverdue > 30) {
            return 'Medium Risk';
        } elseif ($balance > $installment * 3) {
            return 'Low Risk';
        } else {
            return 'Normal';
        }
    }

    protected function calculateLoanAge($createdAt)
    {
        return \Carbon\Carbon::parse($createdAt)->diffInDays(now());
    }

    protected function getLoansSummary($loans)
    {
        $totalBalance = 0;
        $totalInstallment = 0;
        $overdueLoans = 0;
        $activeLoans = 0;

        foreach ($loans as $loan) {
            $totalBalance += $loan['account_balance'];
            $totalInstallment += $loan['installment'];
            
            if (in_array($loan['loan_status'], ['Overdue', 'Seriously Overdue', 'Late'])) {
                $overdueLoans++;
            }
            
            if ($loan['loan_status'] === 'Active') {
                $activeLoans++;
            }
        }

        return [
            'total_balance' => $totalBalance,
            'total_installment' => $totalInstallment,
            'total_loans' => count($loans),
            'overdue_loans' => $overdueLoans,
            'active_loans' => $activeLoans,
            'average_balance' => count($loans) > 0 ? $totalBalance / count($loans) : 0
        ];
    }

    public function getSettlementData()
    {
        try {
            $settlement1 = DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->where('loan_array_id', 1)
                ->first();

            $settlement2 = DB::table('settled_loans')
                ->where('loan_id', session('currentloanID'))
                ->where('loan_array_id', 2)
                ->first();

            return [
                'settlement1' => [
                    'institution' => $settlement1->institution ?? '',
                    'account' => $settlement1->account ?? '',
                    'amount' => $settlement1->amount ?? 0,
                    'exists' => !is_null($settlement1)
                ],
                'settlement2' => [
                    'institution' => $settlement2->institution ?? '',
                    'account' => $settlement2->account ?? '',
                    'amount' => $settlement2->amount ?? 0,
                    'exists' => !is_null($settlement2)
                ],
                'total_amount' => ($settlement1->amount ?? 0) + ($settlement2->amount ?? 0)
            ];
        } catch (\Exception $e) {
            \Log::error('Error getting settlement data: ' . $e->getMessage());
            return [
                'settlement1' => ['institution' => '', 'account' => '', 'amount' => 0, 'exists' => false],
                'settlement2' => ['institution' => '', 'account' => '', 'amount' => 0, 'exists' => false],
                'total_amount' => 0
            ];
        }
    }
} 