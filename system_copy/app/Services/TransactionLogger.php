<?php

namespace App\Services;

use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Auth;

class TransactionLogger {
    private const LOG_CHANNEL = 'transactions';
    private const LOG_LEVELS = [
        'info' => 'INFO',
        'warning' => 'WARNING',
        'error' => 'ERROR',
        'debug' => 'DEBUG'
    ];

    public function logTransactionStart($data) {
        $this->log('info', 'Transaction Started', [
            'user_id' => Auth::id(),
            'timestamp' => now(),
            'transaction_data' => $this->sanitizeData($data),
            'session_id' => session()->getId()
        ]);
    }

    public function logValidationResults($validations) {
        $this->log('info', 'Transaction Validations', [
            'validations' => $validations,
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logBalanceChanges($preBalances, $changes, $postBalances) {
        $this->log('info', 'Balance Changes', [
            'pre_transaction' => $preBalances,
            'changes' => $changes,
            'post_transaction' => $postBalances,
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logAccountUpdate($accountData, $action) {
        $this->log('info', 'Account Update', [
            'account_number' => $accountData['account_number'],
            'previous_balance' => $accountData['previous_balance'],
            'new_balance' => $accountData['new_balance'],
            'action' => $action,
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logTransactionRecord($record) {
        // Validate required fields
        $requiredFields = ['reference_number', 'transaction_type', 'amount', 'debit_account', 'credit_account'];
        $missingFields = array_filter($requiredFields, function($field) use ($record) {
            return !isset($record[$field]);
        });

        if (!empty($missingFields)) {
            $this->log('warning', 'Transaction Record Missing Fields', [
                'missing_fields' => $missingFields,
                'provided_data' => array_keys($record),
                'timestamp' => now(),
                'user_id' => Auth::id()
            ]);
            return;
        }

        $this->log('info', 'Transaction Recorded', [
            'reference_number' => $record['reference_number'] ?? 'N/A',
            'transaction_type' => $record['transaction_type'] ?? 'N/A',
            'amount' => $record['amount'] ?? 0,
            'accounts' => [
                'debit' => $record['debit_account'] ?? 'N/A',
                'credit' => $record['credit_account'] ?? 'N/A'
            ],
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logError($error, $context = []) {
        $this->log('error', 'Transaction Error', [
            'error_message' => $error->getMessage(),
            'error_code' => $error->getCode(),
            'error_file' => $error->getFile(),
            'error_line' => $error->getLine(),
            'error_trace' => $error->getTraceAsString(),
            'context' => $context,
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logIFRSCompliance($complianceData) {
        $this->log('info', 'IFRS Compliance Check', [
            'standard_applied' => $complianceData['standard_applied'],
            'recognition_criteria' => $complianceData['recognition_criteria'],
            'measurement_basis' => $complianceData['measurement_basis'],
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    public function logTransactionCompletion($referenceNumber, $status) {
        $this->log('info', 'Transaction Completed', [
            'reference_number' => $referenceNumber,
            'status' => $status,
            'timestamp' => now(),
            'user_id' => Auth::id()
        ]);
    }

    private function log($level, $message, $context = []) {
        $logData = [
            'level' => self::LOG_LEVELS[$level] ?? 'INFO',
            'message' => $message,
            'context' => $context,
            'timestamp' => now()->toIso8601String(),
            'environment' => config('app.env'),
            'request_id' => request()->header('X-Request-ID') ?? session()->getId() ?? uniqid('req_', true)
        ];

        Log::channel(self::LOG_CHANNEL)->{$level}($message, $logData);
    }

    private function sanitizeData($data) {
        // Remove sensitive information before logging
        $sensitiveFields = ['password', 'token', 'secret'];
        
        if (is_array($data)) {
            foreach ($data as $key => $value) {
                if (in_array($key, $sensitiveFields)) {
                    $data[$key] = '******';
                } elseif (is_array($value)) {
                    $data[$key] = $this->sanitizeData($value);
                }
            }
        }
        
        return $data;
    }
} 