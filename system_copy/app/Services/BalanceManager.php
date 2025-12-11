<?php

namespace App\Services;

use App\Models\AccountsModel;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;
use Exception;

class BalanceManager {
    public function updateBalances($transaction) {
        try {
            Log::info('Starting balance update process', [
                'debit_account' => $transaction['debit_account']->account_number,
                'credit_account' => $transaction['credit_account']->account_number,
                'amount' => $transaction['amount'],
                'timestamp' => now()->toIso8601String()
            ]);

            $preBalances = $this->getPreTransactionBalances($transaction);
            $balanceChanges = $this->calculateBalanceChanges($transaction);
            $postBalances = $this->getPostTransactionBalances($transaction);
            $verification = $this->verifyBalances($transaction);

            Log::info('Balance update completed successfully', [
                'pre_balances' => $preBalances,
                'changes' => $balanceChanges,
                'post_balances' => $postBalances,
                'verification' => $verification
            ]);

            return [
                'pre_transaction_balances' => $preBalances,
                'balance_changes' => $balanceChanges,
                'post_transaction_balances' => $postBalances,
                'balance_verification' => $verification
            ];
        } catch (Exception $e) {
            Log::error('Balance update failed', [
                'error' => $e->getMessage(),
                'debit_account' => $transaction['debit_account']->account_number ?? null,
                'credit_account' => $transaction['credit_account']->account_number ?? null,
                'amount' => $transaction['amount'] ?? null,
                'trace' => $e->getTraceAsString()
            ]);
            throw $e;
        }
    }

    private function getPreTransactionBalances($transaction) {
        return [
            'debit_account' => [
                'account_number' => $transaction['debit_account']->account_number,
                'balance' => $transaction['debit_account']->balance,
                'debit_total' => $transaction['debit_account']->debit,
                'credit_total' => $transaction['debit_account']->credit
            ],
            'credit_account' => [
                'account_number' => $transaction['credit_account']->account_number,
                'balance' => $transaction['credit_account']->balance,
                'debit_total' => $transaction['credit_account']->debit,
                'credit_total' => $transaction['credit_account']->credit
            ]
        ];
    }

    private function calculateBalanceChanges($transaction) {
        $amount = $transaction['amount'];
        $debitAccount = $transaction['debit_account'];
        $creditAccount = $transaction['credit_account'];

        return [
            'debit_account' => [
                'balance_change' => $this->calculateDebitBalanceChange($debitAccount, $amount),
                'debit_change' => $amount,
                'credit_change' => 0
            ],
            'credit_account' => [
                'balance_change' => $this->calculateCreditBalanceChange($creditAccount, $amount),
                'debit_change' => 0,
                'credit_change' => $amount
            ]
        ];
    }

    private function calculateDebitBalanceChange($account, $amount) {
        $result = 0;
        $type = $account->type;
        if (in_array($type, ['asset_accounts', 'expense_accounts'])) {
            $result = $amount;
            Log::info('Debit increases asset/expense', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'debit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        } elseif (in_array($type, ['liability_accounts', 'capital_accounts', 'income_accounts'])) {
            $result = -$amount;
            Log::info('Debit decreases liability/capital/income', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'debit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        } else {
            Log::warning('Debit action: Unknown account type, no change', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'debit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        }
        return $result;
    }

    private function calculateCreditBalanceChange($account, $amount) {
        $result = 0;
        $type = $account->type;
        if (in_array($type, ['asset_accounts', 'expense_accounts'])) {
            $result = -$amount;
            Log::info('Credit decreases asset/expense', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'credit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        } elseif (in_array($type, ['liability_accounts', 'capital_accounts', 'income_accounts'])) {
            $result = $amount;
            Log::info('Credit increases liability/capital/income', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'credit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        } else {
            Log::warning('Credit action: Unknown account type, no change', [
                'account_number' => $account->account_number,
                'account_type' => $type,
                'action' => 'credit',
                'amount' => $amount,
                'resulting_change' => $result
            ]);
        }
        return $result;
    }

    private function getPostTransactionBalances($transaction) {
        $changes = $this->calculateBalanceChanges($transaction);
        $debit = $transaction['debit_account'];
        $credit = $transaction['credit_account'];

        // Helper to get parent accounts
        $getParent = function($account) {
            $category = null;
            $major = null;
            if ($account->parent_account_number) {
                $category = AccountsModel::where('account_number', $account->parent_account_number)->first();
                if ($category && $category->parent_account_number) {
                    $major = AccountsModel::where('account_number', $category->parent_account_number)->first();
                }
            }
            return [$category, $major];
        };

        // Debit side
        list($debitCategory, $debitMajor) = $getParent($debit);
        // Credit side
        list($creditCategory, $creditMajor) = $getParent($credit);

        // Calculate new balances for all
        $debit_new_balance = $debit->balance + $changes['debit_account']['balance_change'];
        $credit_new_balance = $credit->balance + $changes['credit_account']['balance_change'];

        $debit_category_new_balance = $debitCategory ? $debitCategory->balance + $changes['debit_account']['balance_change'] : null;
        $debit_major_new_balance = $debitMajor ? $debitMajor->balance + $changes['debit_account']['balance_change'] : null;
        $credit_category_new_balance = $creditCategory ? $creditCategory->balance + $changes['credit_account']['balance_change'] : null;
        $credit_major_new_balance = $creditMajor ? $creditMajor->balance + $changes['credit_account']['balance_change'] : null;

        return [
            'debit_account' => [
                'account_number' => $debit->account_number,
                'new_balance' => $debit_new_balance,
                'category_account' => $debitCategory ? [
                    'account_number' => $debitCategory->account_number,
                    'new_balance' => $debit_category_new_balance
                ] : null,
                'major_account' => $debitMajor ? [
                    'account_number' => $debitMajor->account_number,
                    'new_balance' => $debit_major_new_balance
                ] : null
            ],
            'credit_account' => [
                'account_number' => $credit->account_number,
                'new_balance' => $credit_new_balance,
                'category_account' => $creditCategory ? [
                    'account_number' => $creditCategory->account_number,
                    'new_balance' => $credit_category_new_balance
                ] : null,
                'major_account' => $creditMajor ? [
                    'account_number' => $creditMajor->account_number,
                    'new_balance' => $credit_major_new_balance
                ] : null
            ]
        ];
    }

    private function verifyBalances($transaction) {
        return [
            'debits_equal_credits' => $this->verifyDebitsEqualCredits($transaction),
            'accounting_equation' => $this->verifyAccountingEquation($transaction),
            'balance_changes_valid' => $this->verifyBalanceChanges($transaction)
        ];
    }

    private function verifyDebitsEqualCredits($transaction) {
        return $transaction['amount'] === $transaction['amount'];
    }

    private function verifyAccountingEquation($transaction) {
        $postBalances = $this->getPostTransactionBalances($transaction);
        
        $totalAssets = $this->calculateTotalAssets($postBalances);
        $totalLiabilities = $this->calculateTotalLiabilities($postBalances);
        $totalEquity = $this->calculateTotalEquity($postBalances);

        return abs($totalAssets - ($totalLiabilities + $totalEquity)) < 0.01;
    }

    private function verifyBalanceChanges($transaction) {
        $changes = $this->calculateBalanceChanges($transaction);
        $postBalances = $this->getPostTransactionBalances($transaction);

        return [
            'debit_account_valid' => $this->verifyAccountBalanceChange($transaction['debit_account'], $changes['debit_account'], $postBalances['debit_account']),
            'credit_account_valid' => $this->verifyAccountBalanceChange($transaction['credit_account'], $changes['credit_account'], $postBalances['credit_account'])
        ];
    }

    private function verifyAccountBalanceChange($account, $changes, $postBalance) {
        $expectedBalance = $account->balance + $changes['balance_change'];
        return abs($expectedBalance - $postBalance['new_balance']) < 0.01;
    }

    private function calculateTotalAssets($balances) {
        // Implement asset total calculation
        return 0;
    }

    private function calculateTotalLiabilities($balances) {
        // Implement liability total calculation
        return 0;
    }

    private function calculateTotalEquity($balances) {
        // Implement equity total calculation
        return 0;
    }

    private function logBalanceChanges($data) {
        Log::info('Balance Changes', $data);
    }

    protected function getCategoryAccount($account)
    {
        try {
            $categoryAccount = DB::table('accounts')
                ->where('account_number', $account->parent_account_number)
                ->first();

            Log::info('Retrieved category account', [
                'account_number' => $account->account_number,
                'category_account' => $categoryAccount ? [
                    'account_number' => $categoryAccount->account_number,
                    'balance' => $categoryAccount->balance
                ] : null
            ]);

            return $categoryAccount;
        } catch (\Exception $e) {
            Log::error('Error retrieving category account', [
                'account_number' => $account->account_number,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }

    protected function getMajorAccount($account)
    {
        try {
            $majorAccount = DB::table('accounts')
                ->where('account_number', substr($account->account_number, 0, 10) . '0000')
                ->first();

            Log::info('Retrieved major account', [
                'account_number' => $account->account_number,
                'major_account' => $majorAccount ? [
                    'account_number' => $majorAccount->account_number,
                    'balance' => $majorAccount->balance
                ] : null
            ]);

            return $majorAccount;
        } catch (\Exception $e) {
            Log::error('Error retrieving major account', [
                'account_number' => $account->account_number,
                'error' => $e->getMessage()
            ]);
            return null;
        }
    }
} 