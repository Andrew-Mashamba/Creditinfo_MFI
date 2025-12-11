<?php

namespace App\Services;

use Carbon\Carbon;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use App\Models\Account;
use App\Models\Transaction;
use App\Models\Member;
use App\Services\NotificationService;
use App\Services\TransactionService;
use App\Services\ReportGenerationService;

class InterestCalculationService
{
    protected $notificationService;
    protected $transactionService;
    protected $reportService;
    protected $savingsInterestRate;
    protected $fixedDepositRates;

    public function __construct(
        // NotificationService $notificationService,
        TransactionService $transactionService,
        ReportGenerationService $reportService
    ) {
        // $this->notificationService = $notificationService;
        $this->transactionService = $transactionService;
        $this->reportService = $reportService;
        $this->savingsInterestRate = config('interest.savings_rate', 3.00); // Default 3%
        $this->fixedDepositRates = config('interest.fixed_deposit_rates', [
            '3_months' => 5.00,
            '6_months' => 6.00,
            '12_months' => 7.00,
            '24_months' => 8.00,
            '36_months' => 9.00
        ]);
    }

    public function calculateDailyInterest($date = null)
    {
        try {
            DB::beginTransaction();

            $date = $date ?? Carbon::now();
            $this->validateInterestCalculationDate($date);

            // Process savings accounts
            $savingsResult = $this->processSavingsAccounts($date);

            // Process fixed deposits
            $fixedDepositResult = $this->processFixedDeposits($date);

            // Generate interest report
            $this->reportService->generateInterestReport($date, [
                'savings' => $savingsResult,
                'fixed_deposits' => $fixedDepositResult
            ]);

            DB::commit();
            Log::info("Daily interest calculation completed for {$date->format('Y-m-d')}");
            return [
                'status' => 'success',
                'savings' => $savingsResult,
                'fixed_deposits' => $fixedDepositResult
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Interest calculation failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function processSavingsAccounts($date)
    {
        $accounts = Account::where('type', 'savings')
            ->where('status', 'active')
            ->get();

        $totalInterest = 0;
        $processedAccounts = 0;

        foreach ($accounts as $account) {
            $interestAmount = $this->calculateSavingsInterest($account, $date);
            
            if ($interestAmount > 0) {
                $this->createInterestTransaction($account, $interestAmount, 'savings');
                $totalInterest += $interestAmount;
                $processedAccounts++;
            }
        }

        return [
            'total_interest' => $totalInterest,
            'processed_accounts' => $processedAccounts
        ];
    }

    protected function processFixedDeposits($date)
    {
        $accounts = Account::where('type', 'fixed_deposit')
            ->where('status', 'active')
            ->get();

        $totalInterest = 0;
        $processedAccounts = 0;

        foreach ($accounts as $account) {
            $interestAmount = $this->calculateFixedDepositInterest($account, $date);
            
            if ($interestAmount > 0) {
                $this->createInterestTransaction($account, $interestAmount, 'fixed_deposit');
                $totalInterest += $interestAmount;
                $processedAccounts++;
            }
        }

        return [
            'total_interest' => $totalInterest,
            'processed_accounts' => $processedAccounts
        ];
    }

    protected function calculateSavingsInterest($account, $date)
    {
        $dailyBalance = $account->balance;
        $dailyRate = $this->savingsInterestRate / 365;
        return ($dailyBalance * $dailyRate) / 100;
    }

    protected function calculateFixedDepositInterest($account, $date)
    {
        $rate = $this->getFixedDepositRate($account->term);
        $dailyRate = $rate / 365;
        return ($account->balance * $dailyRate) / 100;
    }

    protected function getFixedDepositRate($term)
    {
        return $this->fixedDepositRates[$term] ?? $this->fixedDepositRates['12_months'];
    }

    protected function createInterestTransaction($account, $amount, $type)
    {
        $this->transactionService->createTransaction([
            'account_id' => $account->id,
            'amount' => $amount,
            'type' => 'credit',
            'narration' => "Daily interest for {$type} account",
            'reference' => 'INT-' . uniqid(),
            'status' => 'completed'
        ]);

        // Update account balance
        $account->balance += $amount;
        $account->save();

        // Send notification
        // $this->notificationService->sendInterestNotification($account->member, [
        //     'amount' => $amount,
        //     'type' => $type,
        //     'account_number' => $account->account_number
        // ]);
    }

    protected function validateInterestCalculationDate($date)
    {
        if ($date->isWeekend()) {
            throw new \Exception("Interest calculation cannot be performed on weekends");
        }

        if ($date->isHoliday()) {
            throw new \Exception("Interest calculation cannot be performed on holidays");
        }
    }

    public function processMaturedFixedDeposits($date = null)
    {
        try {
            DB::beginTransaction();

            $date = $date ?? Carbon::now();
            $maturedDeposits = Account::where('type', 'fixed_deposit')
                ->where('status', 'active')
                ->where('maturity_date', '<=', $date)
                ->get();

            foreach ($maturedDeposits as $deposit) {
                $this->processMaturedDeposit($deposit, $date);
            }

            DB::commit();
            Log::info("Matured fixed deposits processed successfully for {$date->format('Y-m-d')}");
            return ['status' => 'success'];

        } catch (\Exception $e) {
            DB::rollBack();
            Log::error("Fixed deposit maturity processing failed: " . $e->getMessage());
            throw $e;
        }
    }

    protected function processMaturedDeposit($deposit, $date)
    {
        // Calculate final interest
        $finalInterest = $this->calculateFixedDepositInterest($deposit, $date);
        
        // Create final interest transaction
        $this->createInterestTransaction($deposit, $finalInterest, 'fixed_deposit_maturity');

        // Update deposit status
        $deposit->status = 'matured';
        $deposit->matured_at = $date;
        $deposit->save();

        // Send maturity notification
        // $this->notificationService->sendFixedDepositMaturityNotification($deposit->member, $deposit);
    }
} 