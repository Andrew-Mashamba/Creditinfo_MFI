<?php

namespace App\Services;

use App\Models\AccountHistoricalBalance;
use Illuminate\Support\Facades\DB;
use Carbon\Carbon;

class HistoricalBalanceService
{
    /**
     * Capture current account balances as historical data for a specific year
     */
    public function captureYearEndBalances($year, $capturedBy = null)
    {
        try {
            DB::beginTransaction();

            // Get all Level 2 accounts
            $accounts = DB::table('accounts')
                ->where('account_level', 2)
                ->select([
                    'account_number',
                    'account_name',
                    'major_category_code',
                    'account_level',
                    'type',
                    'balance',
                    'credit',
                    'debit'
                ])
                ->get();

            $capturedCount = 0;

            foreach ($accounts as $account) {
                // Check if historical balance already exists for this year and account
                $existing = AccountHistoricalBalance::where('year', $year)
                    ->where('account_number', $account->account_number)
                    ->first();

                if ($existing) {
                    // Update existing record
                    $existing->update([
                        'account_name' => $account->account_name,
                        'major_category_code' => $account->major_category_code,
                        'account_level' => $account->account_level,
                        'type' => $account->type,
                        'balance' => $account->balance,
                        'credit' => $account->credit,
                        'debit' => $account->debit,
                        'snapshot_date' => now(),
                        'captured_by' => $capturedBy,
                    ]);
                } else {
                    // Create new record
                    AccountHistoricalBalance::create([
                        'year' => $year,
                        'account_number' => $account->account_number,
                        'account_name' => $account->account_name,
                        'major_category_code' => $account->major_category_code,
                        'account_level' => $account->account_level,
                        'type' => $account->type,
                        'balance' => $account->balance,
                        'credit' => $account->credit,
                        'debit' => $account->debit,
                        'snapshot_date' => now(),
                        'captured_by' => $capturedBy,
                    ]);
                }

                $capturedCount++;
            }

            DB::commit();

            return [
                'success' => true,
                'message' => "Successfully captured {$capturedCount} account balances for year {$year}",
                'count' => $capturedCount
            ];

        } catch (\Exception $e) {
            DB::rollBack();
            return [
                'success' => false,
                'message' => 'Error capturing historical balances: ' . $e->getMessage()
            ];
        }
    }

    /**
     * Get historical balances for a specific year and major category
     */
    public function getHistoricalBalances($year, $majorCategoryCode = null)
    {
        $query = AccountHistoricalBalance::where('year', $year)
            ->where('account_level', 2);

        if ($majorCategoryCode) {
            $query->where('major_category_code', $majorCategoryCode);
        }

        return $query->get();
    }

    /**
     * Get available years with historical data
     */
    public function getAvailableYears()
    {
        return AccountHistoricalBalance::select('year')
            ->distinct()
            ->orderBy('year', 'desc')
            ->pluck('year');
    }

    /**
     * Get the most recent historical year
     */
    public function getMostRecentYear()
    {
        return AccountHistoricalBalance::max('year');
    }
} 