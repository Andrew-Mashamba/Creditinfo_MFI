<?php

namespace App\Services;

use Exception;
use Illuminate\Support\Facades\Log;
use App\Models\AccountsModel;

class TransactionValidator {
    private $transactionTypes;

    public function __construct() {
        $this->transactionTypes = new TransactionTypes();
    }

    public function validateTransaction($data) {
        Log::info('Starting transaction validation', [
            'transaction_data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);

        $validations = [
            'basic_validation' => $this->validateBasicData($data),
            'account_validation' => $this->validateAccounts($data),
            'amount_validation' => $this->validateAmount($data),
            'business_rules' => $this->validateBusinessRules($data),
            'ifrs_compliance' => $this->validateIFRSCompliance($data)
        ];

        $this->logValidations($validations);
        
        // Log overall validation result
        $isValid = !in_array(false, array_map(function($validation) {
            return !in_array(false, $validation);
        }, $validations));

        Log::info('Transaction validation completed', [
            'is_valid' => $isValid,
            'validation_details' => $validations,
            'timestamp' => now()->toIso8601String()
        ]);

        return $validations;
    }

    private function validateBasicData($data) {
        Log::info('Starting basic data validation', [
            'data' => $data,
            'timestamp' => now()->toIso8601String()
        ]);

        $validation = [
            'has_required_fields' => isset($data['first_account'], $data['second_account'], $data['amount']),
            'amount_is_positive' => $data['amount'] > 0,
            'accounts_are_different' => $data['first_account'] !== $data['second_account']
        ];

        Log::info('Basic data validation results', [
            'validation' => $validation,
            'timestamp' => now()->toIso8601String()
        ]);

        if (!$validation['has_required_fields']) {
            Log::error('Missing required transaction data', [
                'data' => $data,
                'timestamp' => now()->toIso8601String()
            ]);
            throw new Exception('Missing required transaction data.');
        }

        if (!$validation['amount_is_positive']) {
            Log::error('Transaction amount must be positive', [
                'amount' => $data['amount'],
                'timestamp' => now()->toIso8601String()
            ]);
            throw new Exception('Transaction amount must be positive.');
        }

        if (!$validation['accounts_are_different']) {
            Log::error('Cannot process transaction between the same account', [
                'account' => $data['first_account'],
                'timestamp' => now()->toIso8601String()
            ]);
            throw new Exception('Cannot process transaction between the same account.');
        }

        return $validation;
    }

    private function validateAccounts($data) {
        Log::info('Starting account validation', [
            'first_account' => $data['first_account'],
            'second_account' => $data['second_account'],
            'timestamp' => now()->toIso8601String()
        ]);

        // Get account objects from account numbers
        $firstAccount = AccountsModel::where('account_number', $data['first_account'])->first();
        $secondAccount = AccountsModel::where('account_number', $data['second_account'])->first();

        $validation = [
            'first_account_valid' => $this->validateAccount($firstAccount),
            'second_account_valid' => $this->validateAccount($secondAccount),
            'accounts_compatible' => $this->validateAccountCompatibility($firstAccount, $secondAccount)
        ];

        Log::info('Account validation results', [
            'validation' => $validation,
            'first_account_details' => $firstAccount ? [
                'account_number' => $firstAccount->account_number,
                'account_type' => $firstAccount->type,
                'status' => $firstAccount->status
            ] : null,
            'second_account_details' => $secondAccount ? [
                'account_number' => $secondAccount->account_number,
                'account_type' => $secondAccount->type,
                'status' => $secondAccount->status
            ] : null,
            'timestamp' => now()->toIso8601String()
        ]);

        return $validation;
    }

    private function validateAccount($account) {
        if (is_null($account)) {
            Log::warning('Account not found during validation', [
                'timestamp' => now()->toIso8601String()
            ]);
            return [
                'exists' => false,
                'is_active' => false,
                'has_valid_type' => false
            ];
        }

        // Log the raw account data for debugging
        Log::info('Raw account data for validation', [
            'account_number' => $account->account_number,
            'account_type' => $account->type,
            'status' => $account->status,
            'all_attributes' => $account->getAttributes()
        ]);

        $validTypes = ['asset_accounts', 'liability_accounts', 'capital_accounts', 'income_accounts', 'expense_accounts'];
        $hasValidType = in_array($account->type, $validTypes);

        // Log type validation details
        Log::info('Account type validation details', [
            'account_number' => $account->account_number,
            'actual_type' => $account->type,
            'valid_types' => $validTypes,
            'is_valid_type' => $hasValidType,
            'validation_result' => $hasValidType ? 'Valid account type' : 'Invalid account type'
        ]);

        $validation = [
            'exists' => true,
            'is_active' => $account->status === 'active',
            'has_valid_type' => $hasValidType
        ];

        Log::info('Individual account validation', [
            'account_number' => $account->account_number,
            'account_type' => $account->type,
            'status' => $account->status,
            'validation' => $validation,
            'timestamp' => now()->toIso8601String()
        ]);

        return $validation;
    }

    private function validateAccountCompatibility($firstAccount, $secondAccount) {
        if (!$firstAccount || !$secondAccount) {
            Log::warning('Cannot validate account compatibility - one or both accounts not found', [
                'first_account' => $firstAccount ? $firstAccount->account_number : null,
                'second_account' => $secondAccount ? $secondAccount->account_number : null,
                'timestamp' => now()->toIso8601String()
            ]);
            return false;
        }

        // Log raw account data for compatibility check
        Log::info('Raw account data for compatibility check', [
            'first_account' => [
                'number' => $firstAccount->account_number,
                'type' => $firstAccount->type,
                'all_attributes' => $firstAccount->getAttributes()
            ],
            'second_account' => [
                'number' => $secondAccount->account_number,
                'type' => $secondAccount->type,
                'all_attributes' => $secondAccount->getAttributes()
            ]
        ]);

        $validCombinations = [
            'asset_accounts' => ['asset_accounts', 'liability_accounts', 'capital_accounts', 'income_accounts', 'expense_accounts'],
            'liability_accounts' => ['asset_accounts', 'liability_accounts', 'capital_accounts', 'expense_accounts'],
            'capital_accounts' => ['asset_accounts', 'liability_accounts', 'capital_accounts', 'expense_accounts'],
            'income_accounts' => ['asset_accounts', 'liability_accounts', 'capital_accounts', 'expense_accounts', 'income_accounts'],
            'expense_accounts' => ['asset_accounts', 'liability_accounts', 'capital_accounts', 'income_accounts']
        ];

        $isCompatible = in_array($secondAccount->type, $validCombinations[$firstAccount->type] ?? []);

        // Log detailed compatibility check
        Log::info('Account compatibility check details', [
            'first_account' => [
                'number' => $firstAccount->account_number,
                'type' => $firstAccount->type
            ],
            'second_account' => [
                'number' => $secondAccount->account_number,
                'type' => $secondAccount->type
            ],
            'is_compatible' => $isCompatible,
            'valid_combinations' => $validCombinations[$firstAccount->type] ?? [],
            'compatibility_result' => $isCompatible ? 'Accounts are compatible' : 'Accounts are not compatible',
            'timestamp' => now()->toIso8601String()
        ]);

        return $isCompatible;
    }

    private function validateAmount($data) {
        // Get account objects from account numbers
        $firstAccount = AccountsModel::where('account_number', $data['first_account'])->first();
        $secondAccount = AccountsModel::where('account_number', $data['second_account'])->first();

        return [
            'is_numeric' => is_numeric($data['amount']),
            'is_within_limits' => $this->checkAmountLimits($data['amount']),
            'has_sufficient_balance' => $this->checkSufficientBalance($firstAccount, $data['amount'])
        ];
    }

    private function checkAmountLimits($amount) {
        $maxTransactionLimit = 1000000000; // Example limit
        return $amount <= $maxTransactionLimit;
    }

    private function checkSufficientBalance($account, $amount) {
        if (!$account) {
            Log::error('Account not found when checking balance', [
                'amount' => $amount
            ]);
            return false;
        }

        if (in_array($account->account_type, ['asset_accounts', 'expense_accounts'])) {
            $hasBalance = $account->balance >= $amount;
            Log::info('Balance check result', [
                'account_number' => $account->account_number,
                'account_type' => $account->account_type,
                'current_balance' => $account->balance,
                'required_amount' => $amount,
                'has_sufficient_balance' => $hasBalance
            ]);
            return $hasBalance;
        }
        return true;
    }

    private function validateBusinessRules($data) {
        // Get account objects from account numbers
        $firstAccount = AccountsModel::where('account_number', $data['first_account'])->first();
        $secondAccount = AccountsModel::where('account_number', $data['second_account'])->first();

        return [
            'has_sufficient_balance' => $this->checkBalance($firstAccount, $data['amount']),
            'is_within_limits' => $this->checkLimits($data),
            'is_authorized' => $this->checkAuthorization($data)
        ];
    }

    private function checkBalance($account, $amount) {
        if (!$account) {
            Log::error('Account not found when checking business rules balance', [
                'amount' => $amount
            ]);
            return false;
        }

        // Implement balance checking logic
        return true;
    }

    private function checkLimits($data) {
        // Implement limit checking logic
        return true;
    }

    private function checkAuthorization($data) {
        // Implement authorization checking logic
        return true;
    }

    private function validateIFRSCompliance($data) {
        // Get account objects from account numbers
        $firstAccount = AccountsModel::where('account_number', $data['first_account'])->first();
        $secondAccount = AccountsModel::where('account_number', $data['second_account'])->first();

        if (!$firstAccount || !$secondAccount) {
            Log::error('Accounts not found for IFRS compliance check', [
                'first_account' => $data['first_account'],
                'second_account' => $data['second_account']
            ]);
            return [
                'standard_applied' => null,
                'recognition_criteria' => false,
                'measurement_basis' => false
            ];
        }

        return [
            'standard_applied' => $this->determineStandard($firstAccount, $secondAccount),
            'recognition_criteria' => $this->checkRecognitionCriteria($firstAccount, $secondAccount, $data['amount']),
            'measurement_basis' => $this->checkMeasurementBasis($firstAccount, $secondAccount)
        ];
    }

    private function determineStandard($firstAccount, $secondAccount) {
        // Determine transaction type based on account types
        $transactionType = $this->determineTransactionType($firstAccount, $secondAccount);
        
        $standards = [
            'revenue' => 'IFRS 15',
            'leases' => 'IFRS 16',
            'financial_instruments' => 'IFRS 9',
            'property_plant_equipment' => 'IAS 16',
            'share_capital' => 'IAS 32',
            'general' => 'IAS 1'
        ];

        Log::info('IFRS Standard determination', [
            'first_account_type' => $firstAccount->account_type,
            'second_account_type' => $secondAccount->account_type,
            'determined_transaction_type' => $transactionType,
            'applied_standard' => $standards[$transactionType] ?? $standards['general']
        ]);

        return $standards[$transactionType] ?? $standards['general'];
    }

    private function determineTransactionType($firstAccount, $secondAccount) {
        // Map account types to transaction types
        $accountTypeMap = [
            'asset_accounts' => 'financial_instruments',
            'liability_accounts' => 'financial_instruments',
            'capital_accounts' => 'share_capital',
            'income_accounts' => 'revenue',
            'expense_accounts' => 'general'
        ];

        // Determine transaction type based on the combination of account types
        if ($firstAccount->account_type === 'capital_accounts' || $secondAccount->account_type === 'capital_accounts') {
            return 'share_capital';
        }

        if ($firstAccount->account_type === 'income_accounts' || $secondAccount->account_type === 'income_accounts') {
            return 'revenue';
        }

        if ($firstAccount->account_type === 'asset_accounts' && $secondAccount->account_type === 'liability_accounts') {
            return 'financial_instruments';
        }

        return $accountTypeMap[$firstAccount->account_type] ?? 'general';
    }

    private function checkRecognitionCriteria($firstAccount, $secondAccount, $amount) {
        // Basic recognition criteria check
        $criteria = [
            'has_valid_accounts' => !is_null($firstAccount) && !is_null($secondAccount),
            'amount_is_positive' => $amount > 0,
            'accounts_are_active' => $firstAccount->status === 'active' && $secondAccount->status === 'active'
        ];

        Log::info('Recognition criteria check', [
            'criteria' => $criteria,
            'first_status' => $firstAccount->status,
            'second_status' => $secondAccount->status,
            'amount' => $amount
        ]);

        return !in_array(false, $criteria);
    }

    private function checkMeasurementBasis($firstAccount, $secondAccount) {
        // Basic measurement basis check
        $measurementBasis = [
            'has_valid_accounts' => !is_null($firstAccount) && !is_null($secondAccount),
            'accounts_have_balances' => isset($firstAccount->balance) && isset($secondAccount->balance)
        ];

        Log::info('Measurement basis check', [
            'basis' => $measurementBasis,
            'first_account_balance' => $firstAccount->balance ?? null,
            'second_account_balance' => $secondAccount->balance ?? null
        ]);

        return !in_array(false, $measurementBasis);
    }

    private function logValidations($validations) {
        Log::info('Transaction validation summary', [
            'validations' => $validations,
            'timestamp' => now()->toIso8601String()
        ]);
    }
} 